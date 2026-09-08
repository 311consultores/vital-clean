<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validación para sys_usuarios (SRS §10.3, RF-01).
 *
 * Gestión de usuarios del sistema: solo ADMIN (RF-13 extendido). Antes de
 * esto, las altas/bajas se hacían manualmente por phpMyAdmin.
 */
class UsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // autorización real ya la hace el middleware role:ADMIN en la ruta
    }

    public function rules(): array
    {
        $usuarioId = $this->route('usuario')?->id_usuario;
        $creando = $this->isMethod('post');

        return [
            'username' => ['required', 'string', 'max:50', Rule::unique('sys_usuarios', 'username')->ignore($usuarioId, 'id_usuario')],
            'nombre_completo' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('sys_usuarios', 'email')->ignore($usuarioId, 'id_usuario')],
            'rol' => ['required', Rule::in(['VENDEDOR', 'OPERADOR', 'ADMIN'])],
            'password' => [$creando ? 'required' : 'nullable', 'string', 'min:8'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'username' => 'nombre de usuario',
            'nombre_completo' => 'nombre completo',
            'rol' => 'rol',
            'password' => 'contraseña',
            'activo' => 'usuario activo',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $usuario = $this->route('usuario');

            if (! $usuario || $usuario->id_usuario !== $this->user()->id_usuario) {
                return;
            }

            // Evita que un Administrador se bloquee a sí mismo desactivando
            // su propia cuenta o quitándose el rol ADMIN por accidente.
            if ($this->has('rol') && $this->input('rol') !== $usuario->rol) {
                $validator->errors()->add('rol', 'No puedes cambiar tu propio rol.');
            }

            if (! $this->boolean('activo')) {
                $validator->errors()->add('activo', 'No puedes desactivar tu propia cuenta.');
            }
        });
    }
}
