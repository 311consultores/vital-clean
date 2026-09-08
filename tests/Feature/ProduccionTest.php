<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\NotaRemision;
use App\Models\Servicio;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * CU-03: Control de Producción e Incidencias.
 */
class ProduccionTest extends TestCase
{
    use RefreshDatabase;

    protected function crearFolioEnProceso(int $cantidadEntrada = 5): array
    {
        $cliente = Cliente::factory()->create();
        $servicio = Servicio::factory()->create();
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);

        $orden = NotaRemision::create([
            'folio_fisico' => '02149',
            'id_cliente' => $cliente->id_cliente,
            'id_vendedor' => $vendedor->id_usuario,
            'fecha_recoleccion' => now(),
            'estatus_orden' => 'PROCESO',
            'conteo_bloqueado' => true,
        ]);

        $detalle = $orden->detalle()->create([
            'id_servicio' => $servicio->id_servicio,
            'cantidad_entrada' => $cantidadEntrada,
        ]);

        return compact('orden', 'cliente', 'servicio', 'detalle');
    }

    public function test_operador_puede_buscar_folio_en_proceso(): void
    {
        $operador = Usuario::factory()->create(['rol' => 'OPERADOR']);
        ['orden' => $orden] = $this->crearFolioEnProceso();

        $response = $this->actingAs($operador)->post(route('produccion.iniciar'), ['folio' => '02149']);

        $response->assertRedirect(route('produccion.detalle', $orden));
    }

    public function test_guardar_salida_igual_a_entrada_marca_listo_sin_incidencia(): void
    {
        $operador = Usuario::factory()->create(['rol' => 'OPERADOR']);
        ['orden' => $orden, 'detalle' => $detalle] = $this->crearFolioEnProceso(5);

        $response = $this->actingAs($operador)->post(route('produccion.guardar', $orden), [
            'salidas' => [$detalle->id_detalle => 5],
        ]);

        $response->assertRedirect(route('produccion.buscar'));

        $detalle->refresh();
        $orden->refresh();

        $this->assertSame(5, $detalle->cantidad_salida);
        $this->assertSame('LISTO', $orden->estatus_orden);
        $this->assertCount(0, $detalle->incidencias);
    }

    public function test_salida_menor_a_entrada_genera_incidencia_automatica_de_faltante(): void
    {
        $operador = Usuario::factory()->create(['rol' => 'OPERADOR']);
        ['orden' => $orden, 'detalle' => $detalle] = $this->crearFolioEnProceso(5);

        $this->actingAs($operador)->post(route('produccion.guardar', $orden), [
            'salidas' => [$detalle->id_detalle => 3],
        ]);

        $detalle->refresh();
        $incidencia = $detalle->incidencias()->first();

        $this->assertSame(3, $detalle->cantidad_salida);
        $this->assertNotNull($incidencia);
        $this->assertStringContainsString('Faltante', $incidencia->comentario);
    }

    public function test_salida_menor_con_motivo_explicito_no_duplica_incidencia_automatica(): void
    {
        Storage::fake('incidencias');
        $operador = Usuario::factory()->create(['rol' => 'OPERADOR']);
        ['orden' => $orden, 'detalle' => $detalle] = $this->crearFolioEnProceso(5);

        $this->actingAs($operador)->post(route('produccion.guardar', $orden), [
            'salidas' => [$detalle->id_detalle => 3],
            'dano' => [$detalle->id_detalle => 'Roto'],
            'comentario_dano' => [$detalle->id_detalle => 'se rompió en la secadora'],
        ]);

        $this->assertCount(1, $detalle->incidencias);
        $this->assertStringContainsString('Roto', $detalle->incidencias()->first()->comentario);
    }

    public function test_operador_no_puede_reprocesar_folio_ya_listo(): void
    {
        $operador = Usuario::factory()->create(['rol' => 'OPERADOR']);
        ['orden' => $orden] = $this->crearFolioEnProceso();
        $orden->update(['estatus_orden' => 'LISTO']);

        $response = $this->actingAs($operador)->post(route('produccion.iniciar'), ['folio' => '02149']);

        $response->assertSessionHas('error');
    }

    public function test_admin_puede_reabrir_folio_ya_listo(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);
        ['orden' => $orden, 'detalle' => $detalle] = $this->crearFolioEnProceso(5);
        $orden->update(['estatus_orden' => 'LISTO']);

        $response = $this->actingAs($admin)->post(route('produccion.guardar', $orden), [
            'salidas' => [$detalle->id_detalle => 5],
        ]);

        $response->assertRedirect(route('produccion.buscar'));
        $this->assertSame(5, $detalle->fresh()->cantidad_salida);
    }

    public function test_vendedor_no_tiene_acceso_a_produccion(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);

        $response = $this->actingAs($vendedor)->get(route('produccion.buscar'));

        $response->assertForbidden();
    }

    public function test_grid_de_en_proceso_muestra_boton_cerrar_y_ver(): void
    {
        $operador = Usuario::factory()->create(['rol' => 'OPERADOR']);
        ['orden' => $orden] = $this->crearFolioEnProceso();

        $response = $this->actingAs($operador)->get(route('produccion.buscar'));

        $response->assertOk();
        $response->assertSee($orden->folio_fisico);
        $response->assertSee(route('produccion.detalle', $orden), false);
        $response->assertSee(route('operaciones.ordenes.show', $orden), false);
    }

    public function test_grid_filtra_por_busqueda(): void
    {
        $operador = Usuario::factory()->create(['rol' => 'OPERADOR']);
        ['orden' => $orden] = $this->crearFolioEnProceso();

        $response = $this->actingAs($operador)->get(route('produccion.buscar', ['buscar' => 'NOEXISTE']));

        $response->assertOk();
        // No se busca folio_fisico aquí: el placeholder de ejemplo del
        // formulario ("Ej. 02149...") coincide por casualidad con el folio
        // de prueba y produciría un falso negativo.
        $response->assertDontSee($orden->cliente->nombre_comercial);
    }
}
