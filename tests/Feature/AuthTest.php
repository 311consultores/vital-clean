<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * RF-01: Autenticación por rol (Vendedor, Operador, Admin).
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_and_is_redirected_to_operaciones(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);

        $response = $this->post('/login', [
            'username' => $admin->username,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('operaciones.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_vendedor_can_login_and_is_redirected_to_vendedor_home(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);

        $response = $this->post('/login', [
            'username' => $vendedor->username,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('vendedor.home'));
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        Usuario::factory()->create(['username' => 'admin.test']);

        $response = $this->post('/login', [
            'username' => 'admin.test',
            'password' => 'contraseña-incorrecta',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $usuario = Usuario::factory()->create(['activo' => false]);

        $response = $this->post('/login', [
            'username' => $usuario->username,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_vendedor_cannot_access_panel_administrativo(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);

        $response = $this->actingAs($vendedor)->get('/operaciones');

        $response->assertForbidden();
    }

    public function test_admin_cannot_access_app_de_vendedor(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);

        $response = $this->actingAs($admin)->get('/vendedor');

        $response->assertForbidden();
    }
}
