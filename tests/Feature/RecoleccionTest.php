<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\NotaRemision;
use App\Models\Servicio;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * CU-01: Levantamiento de Orden en Sitio (Anexo App, pantallas 02-08).
 */
class RecoleccionTest extends TestCase
{
    use RefreshDatabase;

    protected function firmaDataUrl(): string
    {
        // PNG 1x1 mínimo válido, codificado en base64.
        return 'data:image/png;base64,'.base64_encode(base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='
        ));
    }

    public function test_vendedor_can_complete_full_recoleccion_flow(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);
        $cliente = Cliente::factory()->create(['estatus_credito' => true]);
        $servicio = Servicio::factory()->create();

        $this->actingAs($vendedor);

        // Paso 1: armado del pedido -> queda en sesión.
        $store = $this->post(route('vendedor.recoleccion.store'), [
            'folio_fisico' => '02149',
            'id_cliente' => $cliente->id_cliente,
            'cantidades' => [$servicio->id_servicio => 5],
        ]);
        $store->assertRedirect(route('vendedor.recoleccion.resumen'));

        // Paso 2: resumen con firma.
        $this->get(route('vendedor.recoleccion.resumen'))->assertOk();

        // Todavía no debe existir el folio en base de datos (solo tras firmar).
        $this->assertDatabaseCount('ope_notas_remision', 0);

        // Paso 3: confirmar con firma -> crea folio.
        $confirmar = $this->post(route('vendedor.recoleccion.confirmar'), [
            'firma' => $this->firmaDataUrl(),
        ]);

        $nota = NotaRemision::first();
        $confirmar->assertRedirect(route('vendedor.recoleccion.exito', $nota));

        $this->assertDatabaseHas('ope_notas_remision', [
            'folio_fisico' => '02149',
            'id_cliente' => $cliente->id_cliente,
            'id_vendedor' => $vendedor->id_usuario,
            'estatus_orden' => 'RUTA',
        ]);
        $this->assertDatabaseHas('ope_detalle_remision', [
            'folio_sistema' => $nota->folio_sistema,
            'id_servicio' => $servicio->id_servicio,
            'cantidad_entrada' => 5,
            'precio_aplicado' => null,
        ]);
        $this->assertNotNull($nota->firma_cliente);
    }

    public function test_folio_fisico_is_required(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);
        $cliente = Cliente::factory()->create();
        $servicio = Servicio::factory()->create();

        $response = $this->actingAs($vendedor)->post(route('vendedor.recoleccion.store'), [
            'id_cliente' => $cliente->id_cliente,
            'cantidades' => [$servicio->id_servicio => 3],
        ]);

        $response->assertSessionHasErrors('folio_fisico');
    }

    public function test_at_least_one_prenda_with_quantity_is_required(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);
        $cliente = Cliente::factory()->create();
        $servicio = Servicio::factory()->create();

        $response = $this->actingAs($vendedor)->post(route('vendedor.recoleccion.store'), [
            'folio_fisico' => '02149',
            'id_cliente' => $cliente->id_cliente,
            'cantidades' => [$servicio->id_servicio => 0],
        ]);

        $response->assertSessionHasErrors('cantidades');
    }

    public function test_rn04_blocks_cliente_con_credito_suspendido(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);
        $cliente = Cliente::factory()->create(['estatus_credito' => false]);
        $servicio = Servicio::factory()->create();

        $response = $this->actingAs($vendedor)->post(route('vendedor.recoleccion.store'), [
            'folio_fisico' => '02149',
            'id_cliente' => $cliente->id_cliente,
            'cantidades' => [$servicio->id_servicio => 3],
        ]);

        $response->assertSessionHasErrors('id_cliente');
    }

    public function test_confirmar_requires_firma(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);
        $cliente = Cliente::factory()->create();
        $servicio = Servicio::factory()->create();

        $this->actingAs($vendedor)->post(route('vendedor.recoleccion.store'), [
            'folio_fisico' => '02149',
            'id_cliente' => $cliente->id_cliente,
            'cantidades' => [$servicio->id_servicio => 2],
        ]);

        $response = $this->post(route('vendedor.recoleccion.confirmar'), []);

        $response->assertSessionHasErrors('firma');
        $this->assertDatabaseCount('ope_notas_remision', 0);
    }

    public function test_vendedor_cannot_see_another_vendedors_pedido(): void
    {
        $vendedorA = Usuario::factory()->create(['rol' => 'VENDEDOR']);
        $vendedorB = Usuario::factory()->create(['rol' => 'VENDEDOR']);
        $cliente = Cliente::factory()->create();

        $nota = NotaRemision::create([
            'folio_fisico' => '02149',
            'id_cliente' => $cliente->id_cliente,
            'id_vendedor' => $vendedorA->id_usuario,
            'fecha_recoleccion' => now(),
            'estatus_orden' => 'RUTA',
        ]);

        $response = $this->actingAs($vendedorB)->get(route('vendedor.pedidos.show', $nota));

        $response->assertForbidden();
    }

    public function test_admin_cannot_access_recoleccion_flow(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);

        $response = $this->actingAs($admin)->get(route('vendedor.recoleccion.create'));

        $response->assertForbidden();
    }

    public function test_pantalla_de_exito_ofrece_boton_de_whatsapp(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);
        $cliente = Cliente::factory()->create(['estatus_credito' => true, 'telefono' => '9997808557']);
        $servicio = Servicio::factory()->create();

        $this->actingAs($vendedor);
        $this->post(route('vendedor.recoleccion.store'), [
            'folio_fisico' => '02149',
            'id_cliente' => $cliente->id_cliente,
            'cantidades' => [$servicio->id_servicio => 5],
        ]);
        $this->post(route('vendedor.recoleccion.confirmar'), ['firma' => $this->firmaDataUrl()]);

        $nota = NotaRemision::first();
        $response = $this->get(route('vendedor.recoleccion.exito', $nota));

        $response->assertOk();
        $response->assertSee('Enviar nota por WhatsApp');
        $response->assertSee('https://wa.me/529997808557', false);
    }

    public function test_pantalla_de_exito_sin_telefono_avisa_en_vez_de_ofrecer_boton(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);
        $cliente = Cliente::factory()->create(['estatus_credito' => true, 'telefono' => null]);
        $servicio = Servicio::factory()->create();

        $this->actingAs($vendedor);
        $this->post(route('vendedor.recoleccion.store'), [
            'folio_fisico' => '02149',
            'id_cliente' => $cliente->id_cliente,
            'cantidades' => [$servicio->id_servicio => 5],
        ]);
        $this->post(route('vendedor.recoleccion.confirmar'), ['firma' => $this->firmaDataUrl()]);

        $nota = NotaRemision::first();
        $response = $this->get(route('vendedor.recoleccion.exito', $nota));

        $response->assertOk();
        $response->assertDontSee('Enviar nota por WhatsApp');
        $response->assertSee('no tiene teléfono registrado');
    }

    public function test_detalle_de_pedido_ofrece_whatsapp_y_pdf(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);
        $cliente = Cliente::factory()->create(['telefono' => '9997808557']);
        $nota = NotaRemision::create([
            'folio_fisico' => '02149',
            'id_cliente' => $cliente->id_cliente,
            'id_vendedor' => $vendedor->id_usuario,
            'fecha_recoleccion' => now(),
            'estatus_orden' => 'RUTA',
        ]);
        $nota->detalle()->create([
            'id_servicio' => Servicio::factory()->create()->id_servicio,
            'cantidad_entrada' => 5,
        ]);

        $response = $this->actingAs($vendedor)->get(route('vendedor.pedidos.show', $nota));

        $response->assertOk();
        $response->assertSee('Enviar nota por WhatsApp');
        $response->assertSee('Ver PDF');
        $response->assertDontSee('Entregar / Cerrar Pedido');
    }

    public function test_detalle_de_pedido_listo_ofrece_boton_de_entregar(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);
        $cliente = Cliente::factory()->create();
        $nota = NotaRemision::create([
            'folio_fisico' => '02149',
            'id_cliente' => $cliente->id_cliente,
            'id_vendedor' => $vendedor->id_usuario,
            'fecha_recoleccion' => now(),
            'estatus_orden' => 'LISTO',
        ]);

        $response = $this->actingAs($vendedor)->get(route('vendedor.pedidos.show', $nota));

        $response->assertOk();
        $response->assertSee('Entregar / Cerrar Pedido');
        $response->assertSee(route('entrega.remision', $nota), false);
    }
}
