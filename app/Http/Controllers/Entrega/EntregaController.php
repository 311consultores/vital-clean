<?php

namespace App\Http\Controllers\Entrega;

use App\Http\Controllers\Controller;
use App\Models\NotaRemision;
use App\Support\WhatsApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * CU-04 — Cierre de Ciclo y Liquidación.
 * Actor: Vendedor / Administrador.
 *
 * Selecciona folios LISTO, presenta la nota de remisión final, captura la
 * firma de recepción y cierra el ciclo a ENTREGADO (RF-09). El SRS también
 * pide "derivar a Cuentas por Cobrar": no existe ese módulo todavía (es
 * CU-06/RF-12, reportes y cierre de caja) — el folio queda marcado como
 * ENTREGADO y disponible para ese trabajo pendiente.
 *
 * Entrega parcial: si en esta auditoría de cierre no se entrega toda la
 * mercancía de una línea, la cantidad que falta se ampara en una subnota
 * nueva (folio_padre -> este folio) para poder facturarla por parcialidades
 * más adelante. La subnota reinicia el flujo completo desde RUTA — la
 * mercancía pendiente vuelve a pasar por Planta (CU-02) y Producción (CU-03)
 * como si fuera un folio nuevo, igual que el levantamiento original (CU-01):
 * cantidad_entrada queda como el estimado inicial y precio_aplicado/subtotal
 * se fijan hasta que Planta la cuente.
 */
class EntregaController extends Controller
{
    protected const ESTATUS_PROCESABLE = 'LISTO';

    public function buscar(Request $request): View
    {
        $listos = NotaRemision::with('cliente')
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

        return view('entrega.buscar', compact('listos'));
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
            return back()->withInput()->with('error', "El folio {$orden->folio_fisico} está en estatus {$orden->estatus_orden}; todavía no está Listo para entrega.");
        }

        return redirect()->route('entrega.remision', $orden);
    }

    public function remision(NotaRemision $orden): View
    {
        $orden->load('cliente', 'detalle.servicio', 'subnotas', 'padre');

        $whatsappUrl = $orden->estatus_orden === 'ENTREGADO'
            ? WhatsApp::linkEntrega($orden)
            : null;
        $pdfUrl = $orden->estatus_orden === 'ENTREGADO'
            ? WhatsApp::linkPdf($orden)
            : null;

        return view('entrega.remision', compact('orden', 'whatsappUrl', 'pdfUrl'));
    }

    public function confirmar(Request $request, NotaRemision $orden): RedirectResponse
    {
        $esAdmin = $request->user()->rol === 'ADMIN';

        if ($orden->estatus_orden !== self::ESTATUS_PROCESABLE && ! $esAdmin) {
            return back()->with('error', 'Este folio ya no está Listo; no se puede cerrar la entrega otra vez.');
        }

        $orden->load('detalle');

        // Cuánto se entrega AHORA por línea (default: la línea completa, que
        // es el caso normal de siempre — un click en "Confirmar Entrega" sin
        // tocar cantidades sigue cerrando el folio completo como antes).
        $entregadoInput = $request->input('entregado', []);
        $reparto = [];
        $esParcial = false;

        foreach ($orden->detalle as $linea) {
            $totalLinea = (int) ($linea->cantidad_salida ?? $linea->cantidad_entrada);
            $entregadoAhora = array_key_exists($linea->id_detalle, $entregadoInput)
                ? max(0, min((int) $entregadoInput[$linea->id_detalle], $totalLinea))
                : $totalLinea;
            $pendiente = $totalLinea - $entregadoAhora;

            if ($pendiente > 0) {
                $esParcial = true;
            }

            $reparto[$linea->id_detalle] = [
                'linea' => $linea,
                'entregado' => $entregadoAhora,
                'pendiente' => $pendiente,
            ];
        }

        $request->validate([
            'firma' => ['required', 'string', 'starts_with:data:image/png;base64,'],
            'entregado' => ['nullable', 'array'],
            'entregado.*' => ['nullable', 'integer', 'min:0'],
            'folio_fisico_subnota' => [Rule::requiredIf($esParcial), 'nullable', 'string', 'max:20'],
        ], [
            'firma.required' => 'Falta capturar la firma de recepción.',
            'folio_fisico_subnota.required' => 'Esta es una entrega parcial: captura el folio físico de la subnota para lo que queda pendiente.',
        ]);

        $firmaBinaria = base64_decode(substr($request->input('firma'), strlen('data:image/png;base64,')));

        $subnota = DB::transaction(function () use ($orden, $reparto, $esParcial, $firmaBinaria, $request) {
            $subnota = null;

            if ($esParcial) {
                // RN-05: la subnota también necesita su propio folio físico.
                $subnota = NotaRemision::create([
                    'folio_fisico' => $request->input('folio_fisico_subnota'),
                    'folio_padre' => $orden->folio_sistema,
                    'id_cliente' => $orden->id_cliente,
                    'id_vendedor' => $orden->id_vendedor,
                    'fecha_recoleccion' => now(),
                    'fecha_entrega_prog' => $orden->fecha_entrega_prog,
                    'estatus_orden' => 'RUTA',
                ]);
            }

            foreach ($reparto as $r) {
                $linea = $r['linea'];

                $linea->update([
                    'cantidad_salida' => $r['entregado'],
                    'subtotal' => $linea->precio_aplicado !== null
                        ? round($r['entregado'] * $linea->precio_aplicado, 2)
                        : null,
                ]);

                if ($r['pendiente'] > 0 && $subnota) {
                    // precio_aplicado/subtotal quedan pendientes: Planta los
                    // vuelve a fijar cuando audite esta subnota (CU-02),
                    // igual que en el levantamiento original (CU-01).
                    $subnota->detalle()->create([
                        'id_servicio' => $linea->id_servicio,
                        'cantidad_entrada' => $r['pendiente'],
                    ]);
                }
            }

            $orden->update([
                'firma_entrega' => $firmaBinaria,
                'estatus_orden' => $orden->estatus_orden === self::ESTATUS_PROCESABLE
                    ? 'ENTREGADO'
                    : $orden->estatus_orden,
            ]);

            return $subnota;
        });

        $mensaje = 'Folio VC-'.str_pad((string) $orden->folio_sistema, 4, '0', STR_PAD_LEFT).' entregado y cerrado. Queda derivado a Cuentas por Cobrar.';

        if ($subnota) {
            $mensaje .= ' Se generó la subnota VC-'.str_pad((string) $subnota->folio_sistema, 4, '0', STR_PAD_LEFT)
                ." (folio físico {$subnota->folio_fisico}) por la mercancía pendiente, para facturar por parcialidades.";
        }

        return redirect()->route('entrega.remision', $orden)->with('status', $mensaje);
    }
}
