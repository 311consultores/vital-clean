<?php

namespace App\Http\Controllers\Operaciones;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClienteRequest;
use App\Models\Cliente;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Pantalla A-02 — Catálogo de Clientes (Anexo Panel Web).
 */
class ClienteController extends Controller
{
    public function index(): View
    {
        $clientes = Cliente::orderBy('nombre_comercial')->paginate(15);

        return view('operaciones.clientes.index', compact('clientes'));
    }

    public function create(): View
    {
        // #3: candidatos para clonar tarifario (solo clientes que ya tienen tarifas capturadas).
        $clientesConTarifas = Cliente::whereHas('tarifas')->orderBy('nombre_comercial')->get();

        return view('operaciones.clientes.create', compact('clientesConTarifas'));
    }

    public function store(ClienteRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $clonarDe = $datos['clonar_tarifario_de'] ?? null;
        unset($datos['clonar_tarifario_de']);

        $cliente = Cliente::create($datos + ['estatus_credito' => $request->boolean('estatus_credito')]);

        if ($clonarDe) {
            $origen = Cliente::with('tarifas')->find($clonarDe);
            foreach ($origen?->tarifas ?? [] as $tarifa) {
                $cliente->tarifas()->create([
                    'id_servicio' => $tarifa->id_servicio,
                    'precio_pactado' => $tarifa->precio_pactado,
                ]);
            }
        }

        return redirect()->route('operaciones.clientes.index')->with('status', 'Cliente creado correctamente.');
    }

    public function edit(Cliente $cliente): View
    {
        return view('operaciones.clientes.edit', compact('cliente'));
    }

    public function update(ClienteRequest $request, Cliente $cliente): RedirectResponse
    {
        $cliente->update($request->validated() + ['estatus_credito' => $request->boolean('estatus_credito')]);

        return redirect()->route('operaciones.clientes.index')->with('status', 'Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        try {
            $cliente->delete();
        } catch (QueryException) {
            // RN-06 (integridad referencial): tiene tarifas u órdenes asociadas.
            return redirect()->route('operaciones.clientes.index')
                ->with('error', 'No se puede eliminar: el cliente tiene tarifas o pedidos asociados. Suspende su crédito en vez de eliminarlo.');
        }

        return redirect()->route('operaciones.clientes.index')->with('status', 'Cliente eliminado.');
    }
}
