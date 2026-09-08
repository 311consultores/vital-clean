<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\NotaRemision;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Catálogo de Usuarios (RF-01 extendido) — solo ADMIN.
 */
class UsuarioCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_puede_crear_usuario(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);

        $response = $this->actingAs($admin)->post(route('operaciones.usuarios.store'), [
            'username' => 'nuevo.vendedor',
            'nombre_completo' => 'Vendedor Nuevo',
            'email' => 'nuevo@vitalclean.mx',
            'rol' => 'VENDEDOR',
            'password' => 'password123',
            'activo' => '1',
        ]);

        $response->assertRedirect(route('operaciones.usuarios.index'));
        $this->assertDatabaseHas('sys_usuarios', [
            'username' => 'nuevo.vendedor',
            'rol' => 'VENDEDOR',
            'activo' => 1,
        ]);

        $creado = Usuario::where('username', 'nuevo.vendedor')->first();
        $this->assertTrue(Hash::check('password123', $creado->password_hash));
    }

    public function test_username_debe_ser_unico(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);
        Usuario::factory()->create(['username' => 'repetido']);

        $response = $this->actingAs($admin)->post(route('operaciones.usuarios.store'), [
            'username' => 'repetido',
            'rol' => 'VENDEDOR',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('username');
    }

    public function test_admin_puede_editar_usuario_sin_cambiar_password(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);
        $usuario = Usuario::factory()->create(['rol' => 'VENDEDOR', 'nombre_completo' => 'Original']);
        $hashOriginal = $usuario->password_hash;

        $response = $this->actingAs($admin)->put(route('operaciones.usuarios.update', $usuario), [
            'username' => $usuario->username,
            'nombre_completo' => 'Actualizado',
            'email' => $usuario->email,
            'rol' => 'VENDEDOR',
            'password' => '',
            'activo' => '1',
        ]);

        $response->assertRedirect(route('operaciones.usuarios.index'));
        $usuario->refresh();
        $this->assertSame('Actualizado', $usuario->nombre_completo);
        $this->assertSame($hashOriginal, $usuario->password_hash);
    }

    public function test_admin_puede_cambiar_password_al_editar(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);
        $usuario = Usuario::factory()->create(['rol' => 'VENDEDOR']);

        $this->actingAs($admin)->put(route('operaciones.usuarios.update', $usuario), [
            'username' => $usuario->username,
            'email' => $usuario->email,
            'rol' => 'VENDEDOR',
            'password' => 'nuevaClave123',
            'activo' => '1',
        ]);

        $usuario->refresh();
        $this->assertTrue(Hash::check('nuevaClave123', $usuario->password_hash));
    }

    public function test_desactivar_usuario_le_impide_iniciar_sesion(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);
        $usuario = Usuario::factory()->create(['rol' => 'VENDEDOR', 'password_hash' => Hash::make('password')]);

        $this->actingAs($admin)->put(route('operaciones.usuarios.update', $usuario), [
            'username' => $usuario->username,
            'email' => $usuario->email,
            'rol' => 'VENDEDOR',
            'password' => '',
            // activo omitido a propósito == desmarcado
        ]);

        $usuario->refresh();
        $this->assertFalse((bool) $usuario->activo);

        // actingAs() deja al admin autenticado entre peticiones dentro del
        // mismo test; hay que cerrar esa sesión antes de probar el login.
        $this->post(route('logout'));

        $login = $this->post(route('login.store'), [
            'username' => $usuario->username,
            'password' => 'password',
        ]);

        $login->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_admin_no_puede_desactivarse_a_si_mismo(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);

        $response = $this->actingAs($admin)->put(route('operaciones.usuarios.update', $admin), [
            'username' => $admin->username,
            'email' => $admin->email,
            'rol' => 'ADMIN',
            'password' => '',
            // activo omitido == intento de desactivarse
        ]);

        $response->assertSessionHasErrors('activo');
        $this->assertTrue((bool) $admin->fresh()->activo);
    }

    public function test_admin_no_puede_cambiar_su_propio_rol(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);

        $response = $this->actingAs($admin)->put(route('operaciones.usuarios.update', $admin), [
            'username' => $admin->username,
            'email' => $admin->email,
            'rol' => 'VENDEDOR',
            'password' => '',
            'activo' => '1',
        ]);

        $response->assertSessionHasErrors('rol');
        $this->assertSame('ADMIN', $admin->fresh()->rol);
    }

    public function test_admin_no_puede_eliminarse_a_si_mismo(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);

        $response = $this->actingAs($admin)->delete(route('operaciones.usuarios.destroy', $admin));

        $response->assertRedirect(route('operaciones.usuarios.index'));
        $this->assertDatabaseHas('sys_usuarios', ['id_usuario' => $admin->id_usuario]);
    }

    public function test_no_se_puede_eliminar_usuario_con_folios_asociados(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);
        $cliente = Cliente::factory()->create();
        NotaRemision::create([
            'folio_fisico' => '02149',
            'id_cliente' => $cliente->id_cliente,
            'id_vendedor' => $vendedor->id_usuario,
            'fecha_recoleccion' => now(),
            'estatus_orden' => 'RUTA',
        ]);

        $response = $this->actingAs($admin)->delete(route('operaciones.usuarios.destroy', $vendedor));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('sys_usuarios', ['id_usuario' => $vendedor->id_usuario]);
    }

    public function test_operador_no_tiene_acceso_al_catalogo_de_usuarios(): void
    {
        $operador = Usuario::factory()->create(['rol' => 'OPERADOR']);

        $response = $this->actingAs($operador)->get(route('operaciones.usuarios.index'));

        $response->assertForbidden();
    }

    public function test_vendedor_no_tiene_acceso_al_catalogo_de_usuarios(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);

        $response = $this->actingAs($vendedor)->get(route('operaciones.usuarios.index'));

        $response->assertForbidden();
    }
}
