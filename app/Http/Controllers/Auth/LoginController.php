<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * RF-01: Autenticación por rol (Vendedor, Operador, Admin) con credenciales
 * únicas. Pantalla de acceso compartida entre el módulo móvil y el panel
 * web (Anexo App pantalla 01 / Anexo Panel Web A-01): el sistema valida
 * credenciales contra sys_usuarios y redirige según rol.
 */
class LoginController extends Controller
{
    public function show(): \Illuminate\View\View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'username' => 'Usuario o contraseña incorrectos.',
            ]);
        }

        $usuario = Auth::user();

        if (! $usuario->activo) {
            Auth::logout();

            throw ValidationException::withMessages([
                'username' => 'Este usuario está deshabilitado. Contacta al administrador.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended($this->redirectPathFor($usuario));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * VENDEDOR -> app móvil (PWA de ruta). ADMIN/OPERADOR -> panel web
     * administrativo (Anexo Panel Web, notas de implementación §11).
     */
    public function redirectPathFor(Usuario $usuario): string
    {
        return match ($usuario->rol) {
            'VENDEDOR' => route('vendedor.home'),
            default => route('operaciones.dashboard'),
        };
    }
}
