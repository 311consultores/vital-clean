<?php

namespace App\Http\Controllers\Notas;

use App\Http\Controllers\Controller;
use App\Models\NotaRemision;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;

/**
 * PDF de la nota de remisión, para compartir con el cliente por WhatsApp
 * (ver App\Support\WhatsApp). La ruta es pública pero exige URL firmada
 * (middleware 'signed'): el cliente externo no tiene cuenta en el sistema,
 * así que no puede autenticarse, pero tampoco queremos que cualquiera
 * adivine folios consecutivos y descargue notas ajenas.
 */
class NotaPdfController extends Controller
{
    public function show(NotaRemision $orden): Response
    {
        $orden->load('cliente', 'detalle.servicio');

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('notas.pdf', compact('orden'))->render());
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $nombreArchivo = 'nota-VC-'.str_pad((string) $orden->folio_sistema, 4, '0', STR_PAD_LEFT).'.pdf';

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$nombreArchivo.'"',
        ]);
    }
}
