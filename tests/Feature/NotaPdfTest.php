<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\NotaRemision;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Support\WhatsApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PDF de la nota de remisión, compartido dentro del mensaje de WhatsApp
 * (App\Support\WhatsApp) como enlace firmado, ya que wa.me no admite
 * adjuntar archivos, solo texto.
 */
class NotaPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function crearFolio(): NotaRemision
    {
        $cliente = Cliente::factory()->create();
        $servicio = Servicio::factory()->create();
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);

        $orden = NotaRemision::create([
            'folio_fisico' => '02149',
            'id_cliente' => $cliente->id_cliente,
            'id_vendedor' => $vendedor->id_usuario,
            'fecha_recoleccion' => now(),
            'estatus_orden' => 'RUTA',
        ]);

        $orden->detalle()->create([
            'id_servicio' => $servicio->id_servicio,
            'cantidad_entrada' => 5,
        ]);

        return $orden;
    }

    public function test_enlace_firmado_descarga_el_pdf_sin_autenticacion(): void
    {
        $orden = $this->crearFolio();

        $url = WhatsApp::linkPdf($orden);
        $response = $this->get($url);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_url_sin_firma_es_rechazada(): void
    {
        $orden = $this->crearFolio();

        $response = $this->get('/notas/'.$orden->folio_sistema.'/pdf');

        $response->assertForbidden();
    }

    public function test_url_con_firma_alterada_es_rechazada(): void
    {
        $orden = $this->crearFolio();

        $url = WhatsApp::linkPdf($orden).'x';
        $response = $this->get($url);

        $response->assertForbidden();
    }

    public function test_mensaje_de_whatsapp_incluye_el_enlace_al_pdf(): void
    {
        $orden = $this->crearFolio();
        $orden->cliente->update(['telefono' => '9997808557']);
        $orden->load('cliente', 'detalle.servicio');

        $link = WhatsApp::linkRecoleccion($orden);

        parse_str(parse_url($link, PHP_URL_QUERY), $query);
        $this->assertStringContainsString('/notas/'.$orden->folio_sistema.'/pdf', $query['text']);
        $this->assertStringContainsString('signature=', $query['text']);
    }
}
