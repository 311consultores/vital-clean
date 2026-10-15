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
            // #9: la vista comprime la foto en el navegador antes de subirla
            // (evita fallos por límites de PHP en hosting compartido con
            // fotos de cámara de 8-15MB), pero el límite se sube por si la
            // compresión no corrió (navegador viejo, JS deshabilitado).
            'foto.*' => ['nullable', 'image', 'max:8192'],
        ];
    }
}
