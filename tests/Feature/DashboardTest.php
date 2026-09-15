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

    public function test_campanita_del_encabezado_muestra_pedidos_nuevos_en_cualquier_pantalla(): void
    {
        // #11: la campanita usa la misma sesión 'dashboard_ultima_visita'
        // que el aviso del dashboard, pero debe verse en cualquier pantalla
        // de Admin/Operador, no solo en el dashboard. Se busca el HTML del
        // badge (no solo el nombre de la clase, que también aparece en el
        // <style> del layout) y se avanza el reloj para no depender de la
        // precisión de segundo de los timestamps en la comparación ">".
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);
        $this->actingAs($admin)->get(route('operaciones.dashboard')); // marca "visto" en t0

        $this->travel(2)->seconds();
        $this->crearOrden('RUTA'); // pedido nuevo después de t0

        $response = $this->actingAs($admin)->get(route('planta.buscar'));

        $response->assertOk();
        $response->assertSee('class="header-bell-badge">1</span>', false);
    }

    public function test_campanita_no_aparece_sin_pedidos_nuevos(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);
        $this->actingAs($admin)->get(route('operaciones.dashboard'));

        $response = $this->actingAs($admin)->get(route('planta.buscar'));

        $response->assertOk();
        $response->assertDontSee('class="header-bell-badge"', false);
    }

    public function test_visitar_el_dashboard_limpia_la_campanita(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);
        $this->actingAs($admin)->get(route('operaciones.dashboard'));

        $this->travel(2)->seconds();
        $this->crearOrden('RUTA');

        // Re-visitar el dashboard "marca como visto" de nuevo.
        $this->actingAs($admin)->get(route('operaciones.dashboard'));

        $response = $this->actingAs($admin)->get(route('planta.buscar'));

        $response->assertDontSee('class="header-bell-badge"', false);
    }

    public function test_vendedor_no_ve_la_campanita_de_pedidos_nuevos(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);

        $response = $this->actingAs($vendedor)->get(route('vendedor.home'));

        $response->assertOk();
        $response->assertDontSee('class="header-bell"', false);
    }
}
