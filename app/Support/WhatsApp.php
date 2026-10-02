<?php

namespace App\Support;

use App\Models\NotaRemision;
use Illuminate\Support\Facades\URL;

/**
 * Genera enlaces "click to chat" de WhatsApp (wa.me) para compartir la nota
 * de remisión con la persona encargada del hotel/comercio, tanto al
 * recolectar (CU-01) como al entregar (CU-04).
 *
 * No usa la API de WhatsApp Business (requeriría credenciales y aprobación
 * de Meta) — abre WhatsApp Web/App con el mensaje ya redactado y es el
 * vendedor quien confirma el envío. No depende de nada fuera de lo que ya
 * hay en el hosting.
 *
 * wa.me no admite adjuntar archivos, solo texto: el "PDF adjunto" es en
 * realidad un enlace firmado y con vencimiento a /notas/{orden}/pdf dentro
 * del mensaje — el cliente lo toca y el PDF se abre/descarga en su celular.
 */
class WhatsApp
{
    /**
     * @return string|null null si el cliente no tiene teléfono registrado.
     */
    public static function linkTo(?string $telefono, string $mensaje): ?string
    {
        $normalizado = self::normalizarTelefono($telefono);

        if (! $normalizado) {
            return null;
        }

        return 'https://wa.me/'.$normalizado.'?text='.rawurlencode($mensaje);
    }

    /**
     * Mensaje que corresponde según el estatus de la nota: de entrega si el
     * ciclo ya cerró (ENTREGADO), de recolección en cualquier otro caso. Es
     * la misma regla que ya aplicaban por separado PedidoController y
     * EntregaController al armar $whatsappUrl.
     */
    public static function mensajePara(NotaRemision $nota): string
    {
        return $nota->estatus_orden === 'ENTREGADO'
            ? self::mensajeEntrega($nota)
            : self::mensajeRecoleccion($nota);
    }

    /**
     * Enlace de WhatsApp para avisar la recolección (CU-01). Espera
     * $nota->cliente y $nota->detalle.servicio ya cargados.
     */
    public static function linkRecoleccion(NotaRemision $nota): ?string
    {
        return self::linkTo($nota->cliente->telefono, self::mensajeRecoleccion($nota));
    }

    /**
     * Enlace de WhatsApp para avisar la entrega (CU-04). Espera
     * $nota->cliente y $nota->detalle.servicio ya cargados.
     */
    public static function linkEntrega(NotaRemision $nota): ?string
    {
        return self::linkTo($nota->cliente->telefono, self::mensajeEntrega($nota));
    }

    /**
     * Texto del mensaje de recolección (CU-01), público para poder
     * reenviarlo a un número capturado a mano (ver mensajePara()) y no solo
     * al teléfono registrado del cliente.
     */
    public static function mensajeRecoleccion(NotaRemision $nota): string
    {
        $pdfUrl = self::linkPdf($nota);

        $lineas = $nota->detalle->map(
            fn ($linea) => "- {$linea->servicio->descripcion}: {$linea->cantidad_entrada}"
        )->implode("\n");

        return "Hola, le confirmamos la *recolección* de su pedido en Lavandería Vital Clean.\n\n"
            ."Cliente: {$nota->cliente->nombre_comercial}\n"
            ."Folio: {$nota->folio_display}\n"
            ."Fecha: {$nota->fecha_recoleccion->format('d/m/Y')}\n\n"
            ."Prendas recolectadas:\n{$lineas}\n\n"
            ."📄 Nota en PDF: {$pdfUrl}\n\n"
            .'Le avisaremos en cuanto esté lista para entrega. ¡Gracias por su preferencia!';
    }

    /**
     * Texto del mensaje de entrega (CU-04), público por el mismo motivo que
     * mensajeRecoleccion().
     */
    public static function mensajeEntrega(NotaRemision $nota): string
    {
        $pdfUrl = self::linkPdf($nota);

        $lineas = $nota->detalle->map(
            fn ($linea) => '- '.$linea->servicio->descripcion.': '.($linea->cantidad_salida ?? $linea->cantidad_entrada)
        )->implode("\n");

        $total = $nota->detalle->every(fn ($l) => $l->subtotal !== null)
            ? '$'.number_format((float) $nota->detalle->sum('subtotal'), 2)
            : 'Pendiente';

        return "Hola, le confirmamos la *entrega* de su pedido de Lavandería Vital Clean.\n\n"
            ."Cliente: {$nota->cliente->nombre_comercial}\n"
            ."Folio: {$nota->folio_display}\n\n"
            ."Prendas entregadas:\n{$lineas}\n\n"
            ."Total: {$total}\n\n"
            ."📄 Nota en PDF: {$pdfUrl}\n\n"
            .'¡Gracias por su preferencia!';
    }

    /**
     * Enlace firmado y con vencimiento (30 días) al PDF de la nota — es lo
     * más parecido a "adjuntar el PDF" que permite un enlace wa.me, que
     * solo acepta texto.
     */
    public static function linkPdf(NotaRemision $nota): string
    {
        return URL::temporarySignedRoute(
            'notas.pdf',
            now()->addDays(30),
            ['orden' => $nota->folio_sistema]
        );
    }

    /**
     * Deja solo dígitos y antepone la lada de México (52) a los últimos 10
     * dígitos. Se asume que todo cliente/número es de México (no hay
     * operación fuera del país) para no tener que lidiar con lada de país
     * distinta — funciona igual si ya traía el 52 de más, un viejo prefijo
     * 044/045, o solo el local a 10 dígitos.
     */
    protected static function normalizarTelefono(?string $telefono): ?string
    {
        $digitos = preg_replace('/\D/', '', (string) $telefono);

        if (strlen($digitos) < 10) {
            return null;
        }

        return '52'.substr($digitos, -10);
    }
}
