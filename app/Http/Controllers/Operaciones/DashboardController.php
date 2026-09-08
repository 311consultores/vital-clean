<?php

namespace App\Http\Controllers\Operaciones;

use App\Http\Controllers\Controller;
use App\Models\Incidencia;
use App\Models\NotaRemision;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Pantalla A-04 — Panel de Operaciones / Monitor de Órdenes (Anexo Panel Web).
 */
class DashboardController extends Controller
{
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
        ];

        $ordenes = NotaRemision::with(['cliente', 'vendedor'])
            ->withSum('detalle as total', 'subtotal')
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

        $alertasCalidad = Incidencia::with('detalle.notaRemision', 'detalle.servicio')
            ->latest('created_at')
            ->take(5)
            ->get();

        return view('operaciones.dashboard', compact('kpis', 'ordenes', 'alertasCalidad'));
    }
}
