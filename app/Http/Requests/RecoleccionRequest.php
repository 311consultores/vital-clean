<?php

namespace App\Http\Requests;

use App\Models\Cliente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Validación del checklist de recolección (CU-01, Anexo App pantallas 04-05).
 *
 * RN-05: folio_fisico obligatorio (puente papel/digital).
 * RN-04: bloqueo si el cliente tiene crédito suspendido.
 */
class RecoleccionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'folio_fisico' => ['required', 'string', 'max:20'],
            'id_cliente' => ['required', 'integer', 'exists:cat_clientes,id_cliente'],
            'fecha_entrega_prog' => ['nullable', 'date', 'after_or_equal:today'],
            'cantidades' => ['required', 'array'],
            'cantidades.*' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $cliente = Cliente::find($this->input('id_cliente'));

            // RN-04: Bloqueo por Crédito Suspendido.
            if ($cliente && ! $cliente->estatus_credito) {
                $validator->errors()->add('id_cliente', 'Este cliente tiene el crédito suspendido; no se puede abrir un nuevo folio.');
            }

            // Pantalla 05: al menos una prenda con cantidad mayor a cero.
            $cantidades = array_filter((array) $this->input('cantidades', []), fn ($c) => (int) $c > 0);

            if (empty($cantidades)) {
                $validator->errors()->add('cantidades', 'Agrega al menos una prenda con cantidad mayor a cero.');
            }
        });
    }
}
