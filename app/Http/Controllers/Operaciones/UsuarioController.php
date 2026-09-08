<?php

namespace App\Http\Controllers\Operaciones;

use App\Http\Controllers\Controller;
use App\Http\Requests\UsuarioRequest;
use App\Models\Usuario;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Catálogo de Usuarios del sistema (RF-01 extendido): antes de este
 * módulo, las altas/bajas y cambios de contraseña se hacían manualmente
 * por phpMyAdmin — ver Bitácora del proyecto.
 */
class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $usuarios = Usuario::when($request->filled('buscar'), function ($query) use ($request) {
            $buscar = $request->input('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('username', 'like', "%{$buscar}%")
                    ->orWhere('nombre_completo', 'like', "%{$buscar}%");
            });
        })
            ->orderBy('username')
            ->paginate(15)
            ->withQueryString();

        return view('operaciones.usuarios.index', compact('usuarios'));
    }

    public function create(): View
    {
        return view('operaciones.usuarios.create');
    }

    public function store(UsuarioRequest $request): RedirectResponse
    {
        Usuario::create([
            'username' => $request->input('username'),
            'nombre_completo' => $request->input('nombre_completo'),
            'email' => $request->input('email'),
            'rol' => $request->input('rol'),
            'password_hash' => Hash::make($request->input('password')),
            'activo' => $request->boolean('activo', true),
        ]);

        return redirect()->route('operaciones.usuarios.index')->with('status', 'Usuario creado correctamente.');
    }

    public function edit(Usuario $usuario): View
    {
        return view('operaciones.usuarios.edit', compact('usuario'));
    }

    public function update(UsuarioRequest $request, Usuario $usuario): RedirectResponse
    {
        $datos = [
            'username' => $request->input('username'),
            'nombre_completo' => $request->input('nombre_completo'),
            'email' => $request->input('email'),
            'rol' => $request->input('rol'),
            'activo' => $request->boolean('activo'),
        ];

        // La contraseña solo se cambia si se capturó una nueva.
        if ($request->filled('password')) {
            $datos['password_hash'] = Hash::make($request->input('password'));
        }

        $usuario->update($datos);

        return redirect()->route('operaciones.usuarios.index')->with('status', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, Usuario $usuario): RedirectResponse
    {
        if ($usuario->id_usuario === $request->user()->id_usuario) {
            return redirect()->route('operaciones.usuarios.index')
                ->with('error', 'No puedes eliminar tu propio usuario.');
        }

        try {
            $usuario->delete();
        } catch (QueryException) {
            // RN-06 (integridad referencial): el usuario tiene folios asociados
            // (ej. un vendedor con recolecciones ya registradas).
            return redirect()->route('operaciones.usuarios.index')
                ->with('error', 'No se puede eliminar: el usuario tiene folios asociados. Desactívalo en vez de eliminarlo.');
        }

        return redirect()->route('operaciones.usuarios.index')->with('status', 'Usuario eliminado.');
    }
}
