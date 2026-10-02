<?php

namespace App\Http\Controllers\Operaciones;

use App\Http\Controllers\Controller;
use App\Models\NotaRemision;
use App\Support\WhatsApp;
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
        $orden->load('cliente', 'vendedor', 'detalle.servicio', 'detalle.incidencias', 'padre', 'subnotas');

        return view('operaciones.ordenes.show', [
            'orden' => $orden,
            'pdfUrl' => WhatsApp::linkPdf($orden),
            // El mensaje de entrega incluye el total cobrado (RN-01/RN-02):
            // esta pantalla la ve también OPERADOR/VENDEDOR, así que el
            // enlace y el texto del mensaje solo se arman para ADMIN en la
            // vista — aquí siempre se calculan porque es barato (no hace
            // consultas extra) y así la vista decide sin duplicar lógica.
            'whatsappUrl' => WhatsApp::linkTo($orden->cliente->telefono, WhatsApp::mensajePara($orden)),
            'whatsappMensaje' => WhatsApp::mensajePara($orden),
        ]);
    }
}
