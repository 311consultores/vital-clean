<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\NotaRemision;
use App\Models\Servicio;
use App\Models\TarifaCliente;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * CU-04: Cierre de Ciclo y Liquidación.
 */
class EntregaTest extends TestCase
{
    use RefreshDatabase;

    protected function firmaDataUrl(): string
    {
        // PNG 1x1 mínimo válido, codificado en base64.
        return 'data:image/png;base64,'.base64_encode(base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='
        ));
    }

    protected function crearFolioListo(): array
    {
        $cliente = Cliente::factory()->create();
        $servicio = Servicio::factory()->create();
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);

        TarifaCliente::create([
            'id_cliente' => $cliente->id_cliente,
            'id_servicio' => $servicio->id_servicio,
            'precio_pactado' => 15.00,
        ]);

        $orden = NotaRemision::create([
            'folio_fisico' => '02149',
            'id_cliente' => $cliente->id_cliente,
            'id_vendedor' => $vendedor->id_usuario,
            'fecha_recoleccion' => now(),
            'estatus_orden' => 'LISTO',
            'conteo_bloqueado' => true,
        ]);

        $detalle = $orden->detalle()->create([
            'id_servicio' => $servicio->id_servicio,
            'cantidad_entrada' => 5,
            'cantidad_salida' => 5,
            'precio_aplicado' => 15.00,
            'subtotal' => 75.00,
        ]);

        return compact('orden', 'cliente', 'servicio', 'detalle', 'vendedor');
    }

    public function test_vendedor_puede_buscar_folio_listo(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);
        ['orden' => $orden] = $this->crearFolioListo();

        $response = $this->actingAs($vendedor)->post(route('entrega.iniciar'), ['folio' => '02149']);

        $response->assertRedirect(route('entrega.remision', $orden));
    }

    public function test_remision_muestra_total_calculado(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);
        ['orden' => $orden] = $this->crearFolioListo();

        $response = $this->actingAs($vendedor)->get(route('entrega.remision', $orden));

        $response->assertOk();
        $response->assertSee('75.00');
    }

    public function test_confirmar_entrega_guarda_firma_y_cierra_ciclo(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);
        ['orden' => $orden] = $this->crearFolioListo();

        $response = $this->actingAs($vendedor)->post(route('entrega.confirmar', $orden), [
            'firma' => $this->firmaDataUrl(),
        ]);

        $response->assertRedirect(route('entrega.remision', $orden));

        $orden->refresh();
        $this->assertSame('ENTREGADO', $orden->estatus_orden);
        $this->assertNotNull($orden->firma_entrega);
    }

    public function test_remision_tras_confirmar_ofrece_boton_de_whatsapp(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);
        ['orden' => $orden, 'cliente' => $cliente] = $this->crearFolioListo();
        $cliente->update(['telefono' => '9997808557']);

        $this->actingAs($vendedor)->post(route('entrega.confirmar', $orden), [
            'firma' => $this->firmaDataUrl(),
        ]);

        $response = $this->actingAs($vendedor)->get(route('entrega.remision', $orden));

        $response->assertOk();
        $response->assertSee('Enviar nota por WhatsApp');
        $response->assertSee('https://wa.me/529997808557', false);
    }

    public function test_remision_sin_telefono_avisa_en_vez_de_ofrecer_boton(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);
        ['orden' => $orden, 'cliente' => $cliente] = $this->crearFolioListo();
        $cliente->update(['telefono' => null]);

        $this->actingAs($vendedor)->post(route('entrega.confirmar', $orden), [
            'firma' => $this->firmaDataUrl(),
        ]);

        $response = $this->actingAs($vendedor)->get(route('entrega.remision', $orden));

        $response->assertOk();
        $response->assertDontSee('Enviar nota por WhatsApp');
        $response->assertSee('no tiene teléfono registrado');
    }

    public function test_confirmar_requiere_firma(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);
        ['orden' => $orden] = $this->crearFolioListo();

        $response = $this->actingAs($vendedor)->post(route('entrega.confirmar', $orden), []);

        $response->assertSessionHasErrors('firma');
        $this->assertSame('LISTO', $orden->fresh()->estatus_orden);
    }

    public function test_no_se_puede_entregar_folio_que_no_esta_listo(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);
        ['orden' => $orden] = $this->crearFolioListo();
        $orden->update(['estatus_orden' => 'PROCESO']);

        $response = $this->actingAs($vendedor)->post(route('entrega.iniciar'), ['folio' => '02149']);

        $response->assertSessionHas('error');
    }

    public function test_operador_no_tiene_acceso_a_entrega(): void
    {
        $operador = Usuario::factory()->create(['rol' => 'OPERADOR']);

        $response = $this->actingAs($operador)->get(route('entrega.buscar'));

        $response->assertForbidden();
    }

    public function test_admin_tiene_acceso_a_entrega(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);

        $response = $this->actingAs($admin)->get(route('entrega.buscar'));

        $response->assertOk();
    }

    public function test_grid_de_listos_muestra_boton_entregar_y_ver(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);
        ['orden' => $orden] = $this->crearFolioListo();

        $response = $this->actingAs($vendedor)->get(route('entrega.buscar'));

        $response->assertOk();
        $response->assertSee($orden->folio_fisico);
        $response->assertSee(route('entrega.remision', $orden), false);
        $response->assertSee(route('operaciones.ordenes.show', $orden), false);
    }

    public function test_vendedor_puede_explorar_el_folio_con_el_boton_ver(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);
        ['orden' => $orden] = $this->crearFolioListo();

        $response = $this->actingAs($vendedor)->get(route('operaciones.ordenes.show', $orden));

        $response->assertOk();
    }
}
