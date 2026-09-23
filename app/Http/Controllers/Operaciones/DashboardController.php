<?php

namespace App\Http\Controllers\Operaciones;

use App\Http\Controllers\Controller;
use App\Models\DetalleRemision;
use App\Models\NotaRemision;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Pantalla A-04 — Panel de Operaciones / Monitor de Órdenes (Anexo Panel Web).
 */
class DashboardController extends Controller
{
    /**
     * Pestaña "En Proceso": desde que se recolecta hasta que queda listo
     * para entregar. La otra pestaña ("Finalizadas") junta ENTREGADO y
     * CANCELADO — son los dos estatus terminales del flujo (RN-07).
     */
    protected const ESTATUS_POR_VISTA = [
        'proceso' => ['RUTA', 'PLANTA_RECIBIDO', 'PROCESO', 'LISTO'],
        'finalizadas' => ['ENTREGADO', 'CANCELADO'],
    ];

    public function index(Request $request): View
    {
        $kpis = [
            'en_ruta' => NotaRemision::where('estatus_orden', 'RUTA')->count(),
            'en_auditoria' => NotaRemision::where('estatus_orden', 'PLANTA_RECIBIDO')->count(),
            'en_proceso' => NotaRemision::where('estatus_orden', 'PROCESO')->count(),
            'listos' => NotaRemision::where('estatus_orden', 'LISTO')->count(),
            'entregados_hoy' => NotaRemision::where('estatus_orden', 'ENTREGADO')
                ->whereDate('updated_at', today())
                ->count(),
            // Proxy simple de "pendiente por cobrar" (el sistema todavía no
            // registra pagos/facturación): valor de todos los pedidos
            // activos (no cancelados) que ya tienen precio calculado.
            'pendiente_cobrar' => DetalleRemision::whereHas(
                'notaRemision',
                fn ($q) => $q->where('estatus_orden', '!=', 'CANCELADO')
            )->sum('subtotal'),
        ];

        $vista = $request->input('vista', 'proceso');
        $estatus = self::ESTATUS_POR_VISTA[$vista] ?? self::ESTATUS_POR_VISTA['proceso'];

        $ordenes = NotaRemision::with(['cliente', 'vendedor'])
            ->withSum('detalle as total', 'subtotal')
            ->whereIn('estatus_orden', $estatus)
            ->when($request->filled('buscar'), function ($query) use ($request) {
                $buscar = $request->input('buscar');
                $query->where(function ($q) use ($buscar) {
                    $q->where('folio_fisico', 'like', "%{$buscar}%")
                        ->orWhereHas('cliente', fn ($c) => $c->where('nombre_comercial', 'like', "%{$buscar}%"));
                });
            })
            ->latest('fecha_recoleccion')
            ->paginate(20)
            ->withQueryString();

        // #11: aviso simple de pedidos nuevos desde la última visita al dashboard
        // (sin infraestructura de tiempo real: se recalcula en cada carga/recarga).
        // Visitar el dashboard es lo que "marca como visto" — por eso la
        // sesión se actualiza aquí y no en la campanita del encabezado
        // (AppServiceProvider), que solo lee este mismo valor.
        $pedidosNuevos = NotaRemision::contarNuevosDesde($request->session()->get('dashboard_ultima_visita'));
        $request->session()->put('dashboard_ultima_visita', now());

        return view('operaciones.dashboard', compact('kpis', 'ordenes', 'pedidosNuevos', 'vista'));
    }
}
