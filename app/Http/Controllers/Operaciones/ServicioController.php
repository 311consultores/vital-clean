<?php

namespace App\Http\Controllers\Operaciones;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServicioRequest;
use App\Models\Servicio;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Pantalla A-03 — Catálogo de Servicios/Prendas (Anexo Panel Web).
 */
class ServicioController extends Controller
{
    public function index(): View
    {
        $servicios = Servicio::orderBy('categoria')->orderBy('descripcion')->paginate(20);

        return view('operaciones.servicios.index', compact('servicios'));
    }

    public function create(): View
    {
        return view('operaciones.servicios.create');
    }

    public function store(ServicioRequest $request): RedirectResponse
    {
        Servicio::create($request->validated());

        return redirect()->route('operaciones.servicios.index')->with('status', 'Servicio creado correctamente.');
    }

    public function edit(Servicio $servicio): View
    {
        return view('operaciones.servicios.edit', compact('servicio'));
    }

    public function update(ServicioRequest $request, Servicio $servicio): RedirectResponse
    {
        $servicio->update($request->validated());

        return redirect()->route('operaciones.servicios.index')->with('status', 'Servicio actualizado correctamente.');
    }

    public function destroy(Servicio $servicio): RedirectResponse
    {
        try {
            $servicio->delete();
        } catch (QueryException) {
            return redirect()->route('operaciones.servicios.index')
                ->with('error', 'No se puede eliminar: el servicio tiene tarifas o pedidos asociados.');
        }

        return redirect()->route('operaciones.servicios.index')->with('status', 'Servicio eliminado.');
    }
}
