<?php

namespace App\Http\Controllers\Operaciones;

use App\Http\Controllers\Controller;
use App\Http\Requests\TarifaClienteRequest;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\TarifaCliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Pantalla A-06 — Tarifas por Cliente (Anexo Panel Web).
 * RN-01: el precio unitario no es global; se consulta por contrato.
 */
class TarifaClienteController extends Controller
{
    public function index(Request $request): View
    {
        $clientes = Cliente::orderBy('nombre_comercial')->get();

        $clienteSeleccionado = $request->integer('cliente') ?: $clientes->first()?->id_cliente;

        $tarifas = TarifaCliente::with('servicio')
            ->when($clienteSeleccionado, fn ($q) => $q->where('id_cliente', $clienteSeleccionado))
            ->get();

        return view('operaciones.tarifas.index', [
            'clientes' => $clientes,
            'clienteSeleccionado' => $clienteSeleccionado,
            'tarifas' => $tarifas,
        ]);
    }

    public function create(): View
    {
        return view('operaciones.tarifas.create', [
            'clientes' => Cliente::orderBy('nombre_comercial')->get(),
            'servicios' => Servicio::orderBy('descripcion')->get(),
        ]);
    }

    public function store(TarifaClienteRequest $request): RedirectResponse
    {
        $tarifa = TarifaCliente::create($request->validated());

        return redirect()->route('operaciones.tarifas.index', ['cliente' => $tarifa->id_cliente])
            ->with('status', 'Tarifa registrada correctamente.');
    }

    public function edit(TarifaCliente $tarifa): View
    {
        return view('operaciones.tarifas.edit', [
            'tarifa' => $tarifa,
            'clientes' => Cliente::orderBy('nombre_comercial')->get(),
            'servicios' => Servicio::orderBy('descripcion')->get(),
        ]);
    }

    public function update(TarifaClienteRequest $request, TarifaCliente $tarifa): RedirectResponse
    {
        $tarifa->update($request->validated());

        return redirect()->route('operaciones.tarifas.index', ['cliente' => $tarifa->id_cliente])
            ->with('status', 'Tarifa actualizada correctamente.');
    }

    public function destroy(TarifaCliente $tarifa): RedirectResponse
    {
        $clienteId = $tarifa->id_cliente;
        $tarifa->delete();

        return redirect()->route('operaciones.tarifas.index', ['cliente' => $clienteId])
            ->with('status', 'Tarifa eliminada.');
    }
}
