<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProduccionRequest;
use App\Models\Incidencia;
use App\Models\NotaRemision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * CU-03 — Control de Producción e Incidencias.
 * Actor: Operador de Producción (comparte el rol OPERADOR con Planta — el
 * diccionario de datos solo define 3 roles, ver Bitácora del proyecto).
 *
 * Da seguimiento a Lavado/Secado/Planchado y valida cantidad de entrada
 * (conteo de Planta, CU-02) vs. cantidad de salida (lo que efectivamente
 * regresa listo). Al guardar, el folio pasa de PROCESO a LISTO (RF-08).
 */
class ProduccionController extends Controller
{
    protected const ESTATUS_PROCESABLE = 'PROCESO';

    public function buscar(Request $request): View
    {
        $enProceso = NotaRemision::with('cliente')
            ->where('estatus_orden', self::ESTATUS_PROCESABLE)
            ->when($request->filled('buscar'), function ($query) use ($request) {
                $buscar = $request->input('buscar');
                $query->where(function ($q) use ($buscar) {
                    $q->where('folio_fisico', 'like', "%{$buscar}%")
                        ->orWhereHas('cliente', fn ($c) => $c->where('nombre_comercial', 'like', "%{$buscar}%"));
                });
            })
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('produccion.buscar', compact('enProceso'));
    }

    public function iniciar(Request $request): RedirectResponse
    {
        $request->validate(['folio' => ['required', 'string']]);

        $folio = trim($request->input('folio'));
        $folioNumerico = (int) preg_replace('/\D/', '', $folio);

        $orden = NotaRemision::where('folio_fisico', $folio)
            ->when($folioNumerico > 0, fn ($q) => $q->orWhere('folio_sistema', $folioNumerico))
            ->first();

        if (! $orden) {
            return back()->withInput()->with('error', "No se encontró ningún folio con \"{$folio}\".");
        }

        $esAdmin = $request->user()->rol === 'ADMIN';

        if ($orden->estatus_orden !== self::ESTATUS_PROCESABLE && ! $esAdmin) {
            return back()->withInput()->with('error', "El folio {$orden->folio_fisico} está en estatus {$orden->estatus_orden}; no está listo para cierre de producción o ya se cerró.");
        }

        return redirect()->route('produccion.detalle', $orden);
    }

    public function detalle(NotaRemision $orden): View
    {
        $orden->load('cliente', 'detalle.servicio', 'detalle.incidencias');

        return view('produccion.detalle', compact('orden'));
    }

    public function guardar(ProduccionRequest $request, NotaRemision $orden): RedirectResponse
    {
        $esAdmin = $request->user()->rol === 'ADMIN';

        if ($orden->estatus_orden !== self::ESTATUS_PROCESABLE && ! $esAdmin) {
            return back()->with('error', 'Este folio ya no está en Proceso; no se puede volver a cerrar producción.');
        }

        $orden->load('detalle');
        $salidas = $request->input('salidas', []);
        $danos = $request->input('dano', []);
        $comentarios = $request->input('comentario_dano', []);

        DB::transaction(function () use ($orden, $salidas, $danos, $comentarios, $request) {
            foreach ($orden->detalle as $linea) {
                if (! array_key_exists($linea->id_detalle, $salidas)) {
                    continue;
                }

                $cantidadSalida = (int) $salidas[$linea->id_detalle];
                $linea->update(['cantidad_salida' => $cantidadSalida]);

                $tipoDano = $danos[$linea->id_detalle] ?? null;
                $comentario = $comentarios[$linea->id_detalle] ?? null;

                // Si falta una prenda (salida < entrada) y el operador no
                // capturó ya un motivo, se registra automáticamente para
                // no perder la validación entrada vs. salida (RF-08/CU-03).
                $faltante = $cantidadSalida < (int) $linea->cantidad_entrada;
                if ($faltante && ! $tipoDano && ! $comentario) {
                    $tipoDano = 'Faltante';
                    $comentario = "Entrada {$linea->cantidad_entrada}, salida {$cantidadSalida}.";
                }

                if ($tipoDano || $comentario) {
                    $rutaFoto = null;
                    $archivoFoto = $request->file("foto.{$linea->id_detalle}");
                    if ($archivoFoto) {
                        $rutaFoto = $archivoFoto->store('', 'incidencias');
                    }

                    Incidencia::create([
                        'id_detalle' => $linea->id_detalle,
                        'foto_evidencia' => $rutaFoto,
                        'comentario' => trim(($tipoDano ?? '').($comentario ? " — {$comentario}" : '')) ?: null,
                    ]);
                }
            }

            // RF-08: PROCESO -> LISTO. No retrocede folios que ya avanzaron
            // más (p. ej. si un ADMIN reabre uno ya ENTREGADO).
            $orden->update([
                'estatus_orden' => $orden->estatus_orden === self::ESTATUS_PROCESABLE
                    ? 'LISTO'
                    : $orden->estatus_orden,
            ]);
        });

        return redirect()->route('produccion.buscar')
            ->with('status', "Folio VC-".str_pad($orden->folio_sistema, 4, '0', STR_PAD_LEFT)." marcado como LISTO para entrega.");
    }
}
