<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecoleccionRequest;
use App\Models\Cliente;
use App\Models\NotaRemision;
use App\Models\Servicio;
use App\Support\WhatsApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * CU-01 — Levantamiento de Orden en Sitio (Anexo App, pantallas 02-08).
 *
 * El checklist inicial del vendedor se guarda en sesión hasta que se
 * captura la firma digital; solo entonces se crea el folio en base de
 * datos (ope_notas_remision / ope_detalle_remision), igual que en el
 * flujo de la app: "el botón APROBADO activa el cierre del folio".
 */
class RecoleccionController extends Controller
{
    protected const SESSION_KEY = 'recoleccion_pendiente';

    public function create(): View
    {
        $clientes = Cliente::where('estatus_credito', true)->orderBy('nombre_comercial')->get();
        $servicios = Servicio::orderBy('categoria')->orderBy('descripcion')->get();

        return view('vendedor.recoleccion.create', compact('clientes', 'servicios'));
    }

    public function store(RecoleccionRequest $request): RedirectResponse
    {
        $cantidades = collect($request->input('cantidades', []))
            ->filter(fn ($cantidad) => (int) $cantidad > 0)
            ->map(fn ($cantidad) => (int) $cantidad);

        $request->session()->put(self::SESSION_KEY, [
            'folio_fisico' => $request->input('folio_fisico'),
            'id_cliente' => (int) $request->input('id_cliente'),
            'fecha_entrega_prog' => $request->input('fecha_entrega_prog'),
            'cantidades' => $cantidades->all(),
        ]);

        return redirect()->route('vendedor.recoleccion.resumen');
    }

    public function resumen(Request $request): View|RedirectResponse
    {
        $pendiente = $request->session()->get(self::SESSION_KEY);

        if (! $pendiente) {
            return redirect()->route('vendedor.recoleccion.create')
                ->with('error', 'No hay un pedido en curso. Empieza de nuevo.');
        }

        $cliente = Cliente::findOrFail($pendiente['id_cliente']);
        $servicios = Servicio::whereIn('id_servicio', array_keys($pendiente['cantidades']))->get()->keyBy('id_servicio');

        $items = collect($pendiente['cantidades'])->map(fn ($cantidad, $idServicio) => [
            'servicio' => $servicios[$idServicio],
            'cantidad' => $cantidad,
        ])->values();

        $totalPiezas = $items->sum('cantidad');

        return view('vendedor.recoleccion.resumen', [
            'cliente' => $cliente,
            'items' => $items,
            'totalPiezas' => $totalPiezas,
            'folioFisico' => $pendiente['folio_fisico'],
        ]);
    }

    public function confirmar(Request $request): RedirectResponse
    {
        $pendiente = $request->session()->get(self::SESSION_KEY);

        if (! $pendiente) {
            return redirect()->route('vendedor.recoleccion.create')
                ->with('error', 'No hay un pedido en curso. Empieza de nuevo.');
        }

        $request->validate([
            'firma' => ['required', 'string', 'starts_with:data:image/png;base64,'],
        ], [
            'firma.required' => 'Falta capturar la firma del cliente.',
        ]);

        // RN-04: revalidar por si el crédito cambió entre el armado y la firma.
        $cliente = Cliente::findOrFail($pendiente['id_cliente']);
        if (! $cliente->estatus_credito) {
            $request->session()->forget(self::SESSION_KEY);

            return redirect()->route('vendedor.recoleccion.create')
                ->with('error', 'Este cliente tiene el crédito suspendido; no se puede cerrar el folio.');
        }

        $firmaBinaria = base64_decode(substr($request->input('firma'), strlen('data:image/png;base64,')));

        $nota = DB::transaction(function () use ($pendiente, $firmaBinaria, $request) {
            $nota = NotaRemision::create([
                'folio_fisico' => $pendiente['folio_fisico'],
                'id_cliente' => $pendiente['id_cliente'],
                'id_vendedor' => $request->user()->id_usuario,
                'fecha_recoleccion' => now(),
                'fecha_entrega_prog' => $pendiente['fecha_entrega_prog'] ?: null,
                'estatus_orden' => 'RUTA',
                'firma_cliente' => $firmaBinaria,
                'geolocalizacion' => $request->input('geolocalizacion'),
            ]);

            foreach ($pendiente['cantidades'] as $idServicio => $cantidad) {
                // precio_aplicado/subtotal quedan pendientes: RN-01/RN-02 los
                // fija el Operador de Planta al contar (CU-02), no el vendedor.
                $nota->detalle()->create([
                    'id_servicio' => $idServicio,
                    'cantidad_entrada' => $cantidad,
                ]);
            }

            return $nota;
        });

        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('vendedor.recoleccion.exito', $nota);
    }

    public function exito(NotaRemision $notaRemision, Request $request): View
    {
        abort_unless($notaRemision->id_vendedor === $request->user()->id_usuario, 403);

        $notaRemision->load('cliente', 'detalle.servicio');

        return view('vendedor.recoleccion.exito', [
            'nota' => $notaRemision,
            'whatsappUrl' => WhatsApp::linkRecoleccion($notaRemision),
            'pdfUrl' => WhatsApp::linkPdf($notaRemision),
        ]);
    }
}
