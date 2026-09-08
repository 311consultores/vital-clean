<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación del cierre de producción (CU-03: Control de Producción e
 * Incidencias — Lavado/Secado/Planchado).
 */
class ProduccionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'salidas' => ['required', 'array'],
            'salidas.*' => ['required', 'integer', 'min:0'],
            'dano' => ['nullable', 'array'],
            'dano.*' => ['nullable', Rule::in(['Quemado', 'Mancha', 'Roto', 'Otro', 'Faltante'])],
            'comentario_dano' => ['nullable', 'array'],
            'comentario_dano.*' => ['nullable', 'string', 'max:255'],
            'foto' => ['nullable', 'array'],
            'foto.*' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
