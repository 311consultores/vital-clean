<?php

namespace App\Http\Controllers\Operaciones;

use App\Http\Controllers\Controller;
use App\Models\NotaRemision;
use Illuminate\View\View;

/**
 * Detalle de una orden desde el Monitor de Operaciones (A-04, botón [Ver]).
 * A diferencia de vendedor.pedidos.show, aquí Admin/Operador pueden ver
 * cualquier folio, no solo los propios.
 */
class OrdenController extends Controller
{
    public function show(NotaRemision $orden): View
    {
        $orden->load('cliente', 'vendedor', 'detalle.servicio', 'detalle.incidencias');

        return view('operaciones.ordenes.show', ['orden' => $orden]);
    }
}
