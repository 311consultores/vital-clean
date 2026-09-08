<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación para rel_tarifas_cliente (SRS §10.1, RN-01: precio pactado por
 * contrato — un cliente no puede tener dos precios vigentes para el mismo
 * servicio).
 */
class TarifaClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tarifaId = $this->route('tarifa')?->id_tarifa;

        return [
            'id_cliente' => ['required', 'exists:cat_clientes,id_cliente'],
            'id_servicio' => [
                'required',
                'exists:cat_servicios,id_servicio',
                Rule::unique('rel_tarifas_cliente', 'id_servicio')
                    ->where('id_cliente', $this->input('id_cliente'))
                    ->ignore($tarifaId, 'id_tarifa'),
            ],
            'precio_pactado' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_servicio.unique' => 'Este cliente ya tiene un precio pactado para ese servicio.',
        ];
    }
}
