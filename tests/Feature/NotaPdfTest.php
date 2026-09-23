<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Incidencia;
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

    public function test_el_pdf_nunca_muestra_precios_sin_importar_el_rol(): void
    {
        // El precio se informa al cliente por su factura, no por la nota de
        // remisión — el PDF no debe mostrar precios para ningún rol.
        $orden = $this->crearFolio();
        $orden->detalle()->first()->update(['precio_aplicado' => 15.00, 'subtotal' => 75.00]);
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);

        $url = WhatsApp::linkPdf($orden);
        $response = $this->actingAs($admin)->get($url);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');

        $html = view('notas.pdf', ['orden' => $orden->load('detalle.servicio', 'detalle.incidencias')])->render();
        $this->assertStringNotContainsString('Precio', $html);
        $this->assertStringNotContainsString('75.00', $html);
    }

    public function test_el_pdf_muestra_el_total_de_piezas(): void
    {
        $orden = $this->crearFolio();

        $html = view('notas.pdf', ['orden' => $orden->load('detalle.servicio', 'detalle.incidencias')])->render();

        $this->assertStringContainsString('Total de piezas: 5', $html);
    }

    public function test_el_pdf_muestra_las_incidencias_reportadas(): void
    {
        $orden = $this->crearFolio();
        $detalle = $orden->detalle()->first();
        Incidencia::create(['id_detalle' => $detalle->id_detalle, 'comentario' => 'Mancha de vino']);

        $html = view('notas.pdf', ['orden' => $orden->load('detalle.servicio', 'detalle.incidencias')])->render();

        $this->assertStringContainsString('Incidencias Reportadas', $html);
        $this->assertStringContainsString('Mancha de vino', $html);
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
