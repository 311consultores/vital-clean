<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\NotaRemision;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pantalla A-04 — Panel de Operaciones / Monitor de Órdenes.
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function crearOrden(string $estatus = 'RUTA', ?string $fechaEntrega = null): NotaRemision
    {
        return NotaRemision::create([
            'folio_fisico' => (string) random_int(10000, 99999),
            'id_cliente' => Cliente::factory()->create()->id_cliente,
            'id_vendedor' => Usuario::factory()->create(['rol' => 'VENDEDOR'])->id_usuario,
            'fecha_recoleccion' => now(),
            'fecha_entrega_prog' => $fechaEntrega,
            'estatus_orden' => $estatus,
        ]);
    }

    public function test_admin_can_view_dashboard(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);

        $response = $this->actingAs($admin)->get(route('operaciones.dashboard'));

        $response->assertOk();
    }

    public function test_operador_can_view_dashboard(): void
    {
        $operador = Usuario::factory()->create(['rol' => 'OPERADOR']);

        $response = $this->actingAs($operador)->get(route('operaciones.dashboard'));

        $response->assertOk();
    }

    public function test_vendedor_cannot_view_dashboard(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);

        $response = $this->actingAs($vendedor)->get(route('operaciones.dashboard'));

        $response->assertForbidden();
    }

    public function test_kpis_count_orders_by_estatus(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);
        $this->crearOrden('RUTA');
        $this->crearOrden('RUTA');
        $this->crearOrden('PLANTA_RECIBIDO');
        $this->crearOrden('LISTO');

        $response = $this->actingAs($admin)->get(route('operaciones.dashboard'));

        $response->assertViewHas('kpis', function ($kpis) {
            return $kpis['en_ruta'] === 2 && $kpis['en_auditoria'] === 1 && $kpis['listos'] === 1;
        });
    }

    public function test_prioridad_alta_para_pedido_vencido(): void
    {
        $orden = $this->crearOrden('RUTA', now()->subDay()->toDateString());

        $this->assertSame('alta', $orden->prioridad);
    }

    public function test_prioridad_media_para_pedido_que_vence_hoy(): void
    {
        $orden = $this->crearOrden('RUTA', now()->toDateString());

        $this->assertSame('media', $orden->prioridad);
    }

    public function test_prioridad_baja_para_pedido_entregado_aunque_este_vencido(): void
    {
        $orden = $this->crearOrden('ENTREGADO', now()->subDay()->toDateString());

        $this->assertSame('baja', $orden->prioridad);
    }

    public function test_dashboard_search_filters_by_folio_or_cliente(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);
        $cliente = Cliente::factory()->create(['nombre_comercial' => 'Hotel Búsqueda Única']);
        NotaRemision::create([
            'folio_fisico' => '99999',
            'id_cliente' => $cliente->id_cliente,
            'id_vendedor' => Usuario::factory()->create(['rol' => 'VENDEDOR'])->id_usuario,
            'fecha_recoleccion' => now(),
            'estatus_orden' => 'RUTA',
        ]);
        $this->crearOrden('RUTA');

        $response = $this->actingAs($admin)->get(route('operaciones.dashboard', ['buscar' => 'Búsqueda Única']));

        $response->assertViewHas('ordenes', fn ($ordenes) => $ordenes->total() === 1);
    }

    public function test_admin_can_view_orden_detail_regardless_of_vendedor(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);
        $orden = $this->crearOrden();

        $response = $this->actingAs($admin)->get(route('operaciones.ordenes.show', $orden));

        $response->assertOk();
    }

    public function test_detalle_de_orden_muestra_timeline_de_estatus(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);
        $orden = $this->crearOrden('PROCESO');

        $response = $this->actingAs($admin)->get(route('operaciones.ordenes.show', $orden));

        $response->assertOk();
        $response->assertSee('class="timeline-step completado"', false);
        $response->assertSee('class="timeline-step actual"', false);
        $response->assertDontSee('<div class="timeline-cancelado">', false);
    }

    public function test_detalle_de_orden_cancelada_muestra_aviso_en_vez_de_timeline(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);
        $orden = $this->crearOrden('CANCELADO');

        $response = $this->actingAs($admin)->get(route('operaciones.ordenes.show', $orden));

        $response->assertOk();
        $response->assertSee('<div class="timeline-cancelado">', false);
        $response->assertDontSee('class="timeline-step', false);
    }
}
