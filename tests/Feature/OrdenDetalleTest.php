<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Incidencia;
use App\Models\NotaRemision;
use App\Models\Servicio;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Detalle de Orden (Admin/Operador): las incidencias, con su evidencia
 * fotográfica, se muestran aquí desde que se movieron del dashboard
 * principal (ver DashboardController).
 */
class OrdenDetalleTest extends TestCase
{
    use RefreshDatabase;

    public function test_detalle_de_orden_muestra_card_de_incidencias_con_foto(): void
    {
        Storage::fake('incidencias');
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);
        $cliente = Cliente::factory()->create();
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);
        $servicio = Servicio::factory()->create(['descripcion' => 'Toalla de Mano']);

        $orden = NotaRemision::create([
            'folio_fisico' => 'PENDIENTE',
            'id_cliente' => $cliente->id_cliente,
            'id_vendedor' => $vendedor->id_usuario,
            'fecha_recoleccion' => now(),
            'estatus_orden' => 'PROCESO',
        ]);
        $detalle = $orden->detalle()->create([
            'id_servicio' => $servicio->id_servicio,
            'cantidad_entrada' => 5,
        ]);

        $foto = UploadedFile::fake()->image('dano.jpg')->store('', 'incidencias');
        Incidencia::create([
            'id_detalle' => $detalle->id_detalle,
            'foto_evidencia' => $foto,
            'comentario' => 'Mancha de aceite',
        ]);

        $response = $this->actingAs($admin)->get(route('operaciones.ordenes.show', $orden));

        $response->assertOk();
        $response->assertSee('Incidencias Reportadas');
        $response->assertSee('Toalla de Mano');
        $response->assertSee('Mancha de aceite');
        $response->assertSee(asset('uploads/incidencias/'.$foto), false);
    }

    public function test_detalle_de_orden_sin_incidencias_no_muestra_la_card(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);
        $cliente = Cliente::factory()->create();
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);

        $orden = NotaRemision::create([
            'folio_fisico' => 'PENDIENTE',
            'id_cliente' => $cliente->id_cliente,
            'id_vendedor' => $vendedor->id_usuario,
            'fecha_recoleccion' => now(),
            'estatus_orden' => 'RUTA',
        ]);
        $orden->detalle()->create([
            'id_servicio' => Servicio::factory()->create()->id_servicio,
            'cantidad_entrada' => 3,
        ]);

        $response = $this->actingAs($admin)->get(route('operaciones.ordenes.show', $orden));

        $response->assertOk();
        $response->assertDontSee('Incidencias Reportadas');
    }

    public function test_admin_ve_pdf_y_whatsapp_desde_el_detalle_de_orden(): void
    {
        // Esta pantalla (botón "Ver" del Monitor de Operaciones) nunca tuvo
        // estos botones: Admin solo podía enviar la nota desde Cierre de
        // Entrega, y solo una vez que el folio ya estaba ENTREGADO.
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);
        $cliente = Cliente::factory()->create(['telefono' => '9997808557']);
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);
        $orden = NotaRemision::create([
            'folio_fisico' => 'PENDIENTE',
            'id_cliente' => $cliente->id_cliente,
            'id_vendedor' => $vendedor->id_usuario,
            'fecha_recoleccion' => now(),
            'estatus_orden' => 'ENTREGADO',
        ]);
        $orden->detalle()->create([
            'id_servicio' => Servicio::factory()->create()->id_servicio,
            'cantidad_entrada' => 3,
            'cantidad_salida' => 3,
            'subtotal' => 30,
        ]);

        $response = $this->actingAs($admin)->get(route('operaciones.ordenes.show', $orden));

        $response->assertOk();
        $response->assertSee('Ver PDF');
        $response->assertSee('Enviar nota por WhatsApp');
        $response->assertSee('https://wa.me/529997808557', false);
        $response->assertSee('Enviar a otro número');
    }

    public function test_operador_no_ve_el_boton_de_whatsapp_en_el_detalle_de_orden(): void
    {
        // RN-01/RN-02: OPERADOR nunca debe ver precios, y el mensaje de
        // WhatsApp de una entrega incluye el total cobrado.
        $operador = Usuario::factory()->create(['rol' => 'OPERADOR']);
        $cliente = Cliente::factory()->create(['telefono' => '9997808557']);
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);
        $orden = NotaRemision::create([
            'folio_fisico' => 'PENDIENTE',
            'id_cliente' => $cliente->id_cliente,
            'id_vendedor' => $vendedor->id_usuario,
            'fecha_recoleccion' => now(),
            'estatus_orden' => 'ENTREGADO',
        ]);
        $orden->detalle()->create([
            'id_servicio' => Servicio::factory()->create()->id_servicio,
            'cantidad_entrada' => 3,
            'cantidad_salida' => 3,
            'subtotal' => 30,
        ]);

        $response = $this->actingAs($operador)->get(route('operaciones.ordenes.show', $orden));

        $response->assertOk();
        $response->assertSee('Ver PDF');
        $response->assertDontSee('Enviar nota por WhatsApp');
        $response->assertDontSee('Enviar a otro número');
    }
}
