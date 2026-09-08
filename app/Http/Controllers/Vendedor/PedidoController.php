<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use App\Models\NotaRemision;
use App\Support\WhatsApp;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Anexo App, pantallas 09-10: seguimiento de los pedidos del vendedor.
 */
class PedidoController extends Controller
{
    public function index(Request $request): View
    {
        $pedidos = NotaRemision::with('cliente')
            ->where('id_vendedor', $request->user()->id_usuario)
            ->when($request->filled('buscar'), function ($query) use ($request) {
                $buscar = $request->input('buscar');
                $query->where(function ($q) use ($buscar) {
                    $q->where('folio_fisico', 'like', "%{$buscar}%")
                        ->orWhereHas('cliente', fn ($c) => $c->where('nombre_comercial', 'like', "%{$buscar}%"));
                });
            })
            ->latest('fecha_recoleccion')
            ->paginate(15);

        return view('vendedor.pedidos.index', compact('pedidos'));
    }

    public function show(NotaRemision $notaRemision, Request $request): View
    {
        abort_unless($notaRemision->id_vendedor === $request->user()->id_usuario, 403);

        $notaRemision->load('cliente', 'detalle.servicio');

        // Una vez entregado, el mensaje/PDF reflejan lo realmente entregado
        // (cantidad de salida, precio, total); antes de eso, lo declarado
        // en la recolección — igual que en las pantallas de éxito/remisión.
        $whatsappUrl = $notaRemision->estatus_orden === 'ENTREGADO'
            ? WhatsApp::linkEntrega($notaRemision)
            : WhatsApp::linkRecoleccion($notaRemision);

        return view('vendedor.pedidos.show', [
            'nota' => $notaRemision,
            'whatsappUrl' => $whatsappUrl,
            'pdfUrl' => WhatsApp::linkPdf($notaRemision),
        ]);
    }
}
