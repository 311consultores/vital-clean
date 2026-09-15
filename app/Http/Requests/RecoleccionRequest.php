<?php

namespace App\Http\Requests;

use App\Models\Cliente;
use App\Models\TarifaCliente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Validación del checklist de recolección (CU-01, Anexo App pantallas 04-05).
 *
 * El folio ya no se captura a mano: el sistema lo autogenera (VC-000X)
 * al confirmar el folio (ver RecoleccionController::confirmar).
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

                return;
            }

            // Bug: RN-01 calcula el precio por tarifa pactada; un artículo
            // sin tarifa para este cliente no debe poder agregarse (el UI ya
            // filtra, pero se revalida aquí por si el catálogo es viejo).
            $serviciosTarifados = TarifaCliente::where('id_cliente', $this->input('id_cliente'))
                ->pluck('id_servicio')
                ->all();

            $noTarifados = array_diff(array_map('intval', array_keys($cantidades)), $serviciosTarifados);

            if (! empty($noTarifados)) {
                $validator->errors()->add('cantidades', 'Uno o más artículos ya no tienen un precio pactado vigente con este cliente. Actualiza la página e inténtalo de nuevo.');
            }
        });
    }
}
