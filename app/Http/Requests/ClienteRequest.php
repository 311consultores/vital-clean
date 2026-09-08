<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación para cat_clientes (SRS §10.1, RF-13).
 */
class ClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // autorización real ya la hace el middleware role:ADMIN en la ruta
    }

    public function rules(): array
    {
        $clienteId = $this->route('cliente')?->id_cliente;

        return [
            'nombre_comercial' => ['required', 'string', 'max:150'],
            'razon_social' => ['nullable', 'string', 'max:150'],
            'rfc' => ['nullable', 'string', 'max:13', Rule::unique('cat_clientes', 'rfc')->ignore($clienteId, 'id_cliente')],
            'direccion' => ['nullable', 'string'],
            'telefono' => ['nullable', 'string', 'max:15'],
            'email_facturacion' => ['nullable', 'email', 'max:100'],
            'estatus_credito' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre_comercial' => 'nombre comercial',
            'razon_social' => 'razón social',
            'email_facturacion' => 'email de facturación',
            'estatus_credito' => 'estatus de crédito',
        ];
    }
}
