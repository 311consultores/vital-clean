<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * "/" no tiene contenido propio; redirige al login (RF-01).
     */
    public function test_the_application_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * Regresión: un usuario autenticado visitando "/" o "/login" no debe
     * entrar en un bucle de redirecciones ("/" -> login -> guest middleware
     * -> "/" -> ...). Ver incidente de despliegue en producción.
     */
    public function test_authenticated_admin_visiting_root_does_not_loop(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);

        $response = $this->actingAs($admin)->get('/');

        $response->assertRedirect(route('operaciones.dashboard'));
    }

    public function test_authenticated_vendedor_visiting_root_does_not_loop(): void
    {
        $vendedor = Usuario::factory()->create(['rol' => 'VENDEDOR']);

        $response = $this->actingAs($vendedor)->get('/');

        $response->assertRedirect(route('vendedor.home'));
    }

    public function test_authenticated_user_visiting_login_does_not_loop(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'ADMIN']);

        $response = $this->actingAs($admin)->get('/login');

        $response->assertRedirect();
        $this->assertNotSame('/login', parse_url((string) $response->headers->get('Location'), PHP_URL_PATH));
    }
}
