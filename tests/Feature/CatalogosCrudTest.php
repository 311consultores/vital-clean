<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\TarifaCliente;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * CRUD de catálogos (Anexo Panel Web A-02, A-03, A-06). Solo ADMIN puede
 * gestionarlos; OPERADOR ve el dashboard pero no los catálogos.
 */
class CatalogosCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_cliente(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);

        $response = $this->actingAs($admin)->post(route('operaciones.clientes.store'), [
            'nombre_comercial' => 'Hotel Test',
            'rfc' => 'HTE850101ABC',
            'estatus_credito' => '1',
        ]);

        $response->assertRedirect(route('operaciones.clientes.index'));
        $this->assertDatabaseHas('cat_clientes', ['nombre_comercial' => 'Hotel Test', 'estatus_credito' => 1]);
    }

    public function test_rfc_must_be_unique(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);
        Cliente::factory()->create(['rfc' => 'DUP850101ABC']);

        $response = $this->actingAs($admin)->post(route('operaciones.clientes.store'), [
            'nombre_comercial' => 'Otro Hotel',
            'rfc' => 'DUP850101ABC',
        ]);

        $response->assertSessionHasErrors('rfc');
    }

    public function test_operador_cannot_access_catalogos(): void
    {
        $operador = Usuario::factory()->create(['rol' => 'OPERADOR']);

        $this->actingAs($operador)->get(route('operaciones.clientes.index'))->assertForbidden();
        $this->actingAs($operador)->get(route('operaciones.servicios.index'))->assertForbidden();
        $this->actingAs($operador)->get(route('operaciones.tarifas.index'))->assertForbidden();
    }

    public function test_admin_can_create_servicio(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);

        $response = $this->actingAs($admin)->post(route('operaciones.servicios.store'), [
            'descripcion' => 'Cobija Extra',
            'unidad' => 'PZA',
            'categoria' => 'Hotelería',
        ]);

        $response->assertRedirect(route('operaciones.servicios.index'));
        $this->assertDatabaseHas('cat_servicios', ['descripcion' => 'Cobija Extra']);
    }

    public function test_servicio_descripcion_must_be_unique(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);
        Servicio::factory()->create(['descripcion' => 'Toalla Duplicada']);

        $response = $this->actingAs($admin)->post(route('operaciones.servicios.store'), [
            'descripcion' => 'Toalla Duplicada',
            'unidad' => 'PZA',
        ]);

        $response->assertSessionHasErrors('descripcion');
    }

    public function test_admin_can_create_tarifa(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);
        $cliente = Cliente::factory()->create();
        $servicio = Servicio::factory()->create();

        $response = $this->actingAs($admin)->post(route('operaciones.tarifas.store'), [
            'id_cliente' => $cliente->id_cliente,
            'id_servicio' => $servicio->id_servicio,
            'precio_pactado' => '25.50',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('rel_tarifas_cliente', [
            'id_cliente' => $cliente->id_cliente,
            'id_servicio' => $servicio->id_servicio,
            'precio_pactado' => 25.50,
        ]);
    }

    public function test_cannot_duplicate_tarifa_for_same_cliente_and_servicio(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);
        $tarifa = TarifaCliente::factory()->create();

        $response = $this->actingAs($admin)->post(route('operaciones.tarifas.store'), [
            'id_cliente' => $tarifa->id_cliente,
            'id_servicio' => $tarifa->id_servicio,
            'precio_pactado' => '10.00',
        ]);

        $response->assertSessionHasErrors('id_servicio');
    }

    public function test_deleting_cliente_with_tarifas_is_blocked(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);
        $tarifa = TarifaCliente::factory()->create();

        $response = $this->actingAs($admin)->delete(route('operaciones.clientes.destroy', $tarifa->id_cliente));

        $response->assertRedirect(route('operaciones.clientes.index'));
        $this->assertDatabaseHas('cat_clientes', ['id_cliente' => $tarifa->id_cliente]);
    }
}
