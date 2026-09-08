<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\DetalleRemision;
use App\Models\NotaRemision;
use App\Models\Servicio;
use App\Models\TarifaCliente;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * CU-02: Auditoría de Conteo y Valoración (Planta).
 */
class AuditoriaPlantaTest extends TestCase
{
    use RefreshDatabase;

    protected function crearFolioEnRuta(int $cantidadDeclarada = 5): array
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

        $detalle = $orden->detalle()->create([
            'id_servicio' => $servicio->id_servicio,
            'cantidad_entrada' => $cantidadDeclarada,
        ]);

        return compact('orden', 'cliente', 'servicio', 'detalle');
    }

    public function test_operador_can_search_folio_and_reach_conteo(): void
    {
        $operador = Usuario::factory()->create(['rol' => 'OPERADOR']);
        ['orden' => $orden] = $this->crearFolioEnRuta();

        $response = $this->actingAs($operador)->post(route('planta.iniciar'), ['folio' => '02149']);

        $response->assertRedirect(route('planta.conteo', $orden->fresh()));
    }

    public function test_abrir_pantalla_de_conteo_marca_la_llegada_a_planta(): void
    {
        // La transición RUTA -> PLANTA_RECIBIDO vive en conteo() (no en
        // iniciar()) para que se dispare igual si se entra tecleando el
        // folio o dando clic en "Auditar" desde el grid de pendientes.
        $operador = Usuario::factory()->create(['rol' => 'OPERADOR']);
        ['orden' => $orden] = $this->crearFolioEnRuta();

        $this->actingAs($operador)->get(route('planta.conteo', $orden));

        $this->assertSame('PLANTA_RECIBIDO', $orden->fresh()->estatus_orden);
    }

    public function test_grid_de_pendientes_permite_auditar_directamente(): void
    {
        $operador = Usuario::factory()->create(['rol' => 'OPERADOR']);
        ['orden' => $orden] = $this->crearFolioEnRuta();

        $response = $this->actingAs($operador)->get(route('planta.buscar'));

        $response->assertOk();
        $response->assertSee($orden->folio_fisico);
        $response->assertSee(route('planta.conteo', $orden), false);
        $response->assertSee(route('operaciones.ordenes.show', $orden), false);
    }

    public function test_search_by_folio_sistema_also_works(): void
    {
        $operador = Usuario::factory()->create(['rol' => 'OPERADOR']);
        ['orden' => $orden] = $this->crearFolioEnRuta();

        $response = $this->actingAs($operador)->post(route('planta.iniciar'), [
            'folio' => 'VC-'.str_pad($orden->folio_sistema, 4, '0', STR_PAD_LEFT),
        ]);

        $response->assertRedirect(route('planta.conteo', $orden->fresh()));
    }

    public function test_folio_not_found_shows_error(): void
    {
        $operador = Usuario::factory()->create(['rol' => 'OPERADOR']);

        $response = $this->actingAs($operador)->post(route('planta.iniciar'), ['folio' => 'NOEXISTE']);

        $response->assertSessionHas('error');
    }

    public function test_guardar_aplica_tarifa_pactada_y_bloquea_conteo(): void
    {
        $operador = Usuario::factory()->create(['rol' => 'OPERADOR']);
        ['orden' => $orden, 'cliente' => $cliente, 'servicio' => $servicio, 'detalle' => $detalle] = $this->crearFolioEnRuta(5);

        TarifaCliente::create([
            'id_cliente' => $cliente->id_cliente,
            'id_servicio' => $servicio->id_servicio,
            'precio_pactado' => 20.00,
        ]);

        $response = $this->actingAs($operador)->post(route('planta.guardar', $orden), [
            'cantidades' => [$detalle->id_detalle => 7],
        ]);

        $response->assertRedirect(route('planta.buscar'));

        $detalle->refresh();
        $orden->refresh();

        $this->assertSame(7, $detalle->cantidad_entrada);
        $this->assertEquals(20.00, $detalle->precio_aplicado);
        $this->assertEquals(140.00, $detalle->subtotal);
        $this->assertTrue((bool) $orden->conteo_bloqueado);
        $this->assertSame('PROCESO', $orden->estatus_orden);
    }

    public function test_sin_tarifa_pactada_precio_queda_pendiente(): void
    {
        $operador = Usuario::factory()->create(['rol' => 'OPERADOR']);
        ['orden' => $orden, 'detalle' => $detalle] = $this->crearFolioEnRuta(5);
        // Sin TarifaCliente creada a propósito.

        $this->actingAs($operador)->post(route('planta.guardar', $orden), [
            'cantidades' => [$detalle->id_detalle => 5],
        ]);

        $detalle->refresh();
        $this->assertNull($detalle->precio_aplicado);
        $this->assertNull($detalle->subtotal);
    }

    public function test_rn03_operador_no_puede_modificar_conteo_bloqueado(): void
    {
        $operador = Usuario::factory()->create(['rol' => 'OPERADOR']);
        ['orden' => $orden, 'detalle' => $detalle] = $this->crearFolioEnRuta(5);
        $orden->update(['conteo_bloqueado' => true, 'estatus_orden' => 'PROCESO']);

        $response = $this->actingAs($operador)->post(route('planta.guardar', $orden), [
            'cantidades' => [$detalle->id_detalle => 99],
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(5, $detalle->fresh()->cantidad_entrada);
    }

    public function test_rn03_admin_puede_modificar_conteo_bloqueado(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);
        ['orden' => $orden, 'detalle' => $detalle] = $this->crearFolioEnRuta(5);
        $orden->update(['conteo_bloqueado' => true, 'estatus_orden' => 'PROCESO']);

        $response = $this->actingAs($admin)->post(route('planta.guardar', $orden), [
            'cantidades' => [$detalle->id_detalle => 8],
        ]);

        $response->assertRedirect(route('planta.buscar'));
        $this->assertSame(8, $detalle->fresh()->cantidad_entrada);
        // No debe retroceder un estatus que ya había avanzado más allá.
        $this->assertSame('PROCESO', $orden->fresh()->estatus_orden);
    }

    public function test_reportar_incidencia_con_foto(): void
    {
        Storage::fake('incidencias');
        $operador = Usuario::factory()->create(['rol' => 'OPERADOR']);
        ['orden' => $orden, 'detalle' => $detalle] = $this->crearFolioEnRuta(5);

        $foto = UploadedFile::fake()->image('dano.jpg');

        $this->actingAs($operador)->post(route('planta.guardar', $orden), [
            'cantidades' => [$detalle->id_detalle => 5],
            'dano' => [$detalle->id_detalle => 'Mancha'],
            'comentario_dano' => [$detalle->id_detalle => 'mancha de vino'],
            'foto' => [$detalle->id_detalle => $foto],
        ]);

        $incidencia = $detalle->incidencias()->first();
        $this->assertNotNull($incidencia);
        $this->assertStringContainsString('Mancha', $incidencia->comentario);
        $this->assertStringContainsString('mancha de vino', $incidencia->comentario);
        $this->assertNotNull($incidencia->foto_evidencia);
        Storage::disk('incidencias')->assertExists($incidencia->foto_evidencia);
    }

    public function test_vendedor_cannot_access_planta(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);

        $response = $this->actingAs($vendedor)->get(route('planta.buscar'));

        $response->assertForbidden();
    }

    public function test_ya_procesado_no_permite_reauditar_a_operador(): void
    {
        $operador = Usuario::factory()->create(['rol' => 'OPERADOR']);
        ['orden' => $orden] = $this->crearFolioEnRuta();
        $orden->update(['estatus_orden' => 'LISTO']);

        $response = $this->actingAs($operador)->post(route('planta.iniciar'), ['folio' => '02149']);

        $response->assertSessionHas('error');
    }
}
