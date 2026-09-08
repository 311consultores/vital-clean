<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación del conteo en planta (CU-02, GUI §12.2).
 */
class ConteoPlantaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cantidades' => ['required', 'array'],
            'cantidades.*' => ['required', 'integer', 'min:0'],
            'dano' => ['nullable', 'array'],
            'dano.*' => ['nullable', Rule::in(['Quemado', 'Mancha', 'Roto', 'Otro'])],
            'comentario_dano' => ['nullable', 'array'],
            'comentario_dano.*' => ['nullable', 'string', 'max:255'],
            'foto' => ['nullable', 'array'],
            'foto.*' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
