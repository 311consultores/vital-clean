<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación para cat_servicios (SRS §10.1).
 */
class ServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $servicioId = $this->route('servicio')?->id_servicio;

        return [
            'descripcion' => ['required', 'string', 'max:100', Rule::unique('cat_servicios', 'descripcion')->ignore($servicioId, 'id_servicio')],
            'unidad' => ['required', Rule::in(['PZA', 'KG'])],
            'categoria' => ['nullable', 'string', 'max:50'],
        ];
    }
}
