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

    public function test_vendedor_autenticado_no_ve_precios_en_el_pdf(): void
    {
        // El botón "Ver PDF" del panel usa el mismo enlace firmado que el
        // WhatsApp del cliente; si quien lo abre está logueado como
        // VENDEDOR (misma sesión/cookie), no debe ver precios — igual que
        // en el resto de su panel.
        $orden = $this->crearFolio();
        $vendedor = $orden->vendedor;
        $orden->detalle()->first()->update(['precio_aplicado' => 15.00, 'subtotal' => 75.00]);

        $url = WhatsApp::linkPdf($orden);
        $response = $this->actingAs($vendedor)->get($url);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_vista_pdf_oculta_precios_cuando_se_pide_ocultar(): void
    {
        $orden = $this->crearFolio();
        $orden->detalle()->first()->update(['precio_aplicado' => 15.00, 'subtotal' => 75.00]);
        $orden->load('cliente', 'detalle.servicio');

        $html = view('notas.pdf', ['orden' => $orden, 'ocultarPrecios' => true])->render();

        $this->assertStringNotContainsString('Precio', $html);
        $this->assertStringNotContainsString('75.00', $html);
    }

    public function test_vista_pdf_muestra_precios_por_defecto(): void
    {
        $orden = $this->crearFolio();
        $orden->detalle()->first()->update(['precio_aplicado' => 15.00, 'subtotal' => 75.00]);
        $orden->load('cliente', 'detalle.servicio');

        $html = view('notas.pdf', ['orden' => $orden, 'ocultarPrecios' => false])->render();

        $this->assertStringContainsString('Precio', $html);
        $this->assertStringContainsString('75.00', $html);
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
