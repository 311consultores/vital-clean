@extends('layouts.app')

@section('title', 'Vital Clean — Tarifas por Cliente')

@section('content')
    <div class="page-header">
        <h1>Tarifas por Cliente</h1>
        <a href="{{ route('operaciones.tarifas.create') }}" class="btn">+ Nueva Tarifa</a>
    </div>

    <div class="card" style="margin-bottom:1rem;">
        <form method="GET" action="{{ route('operaciones.tarifas.index') }}" class="form-group" style="margin:0;">
            <label for="cliente">Seleccionar Cliente</label>
            <select id="cliente" name="cliente" onchange="this.form.submit()">
                @forelse ($clientes as $cliente)
                    <option value="{{ $cliente->id_cliente }}" {{ (int) $clienteSeleccionado === $cliente->id_cliente ? 'selected' : '' }}>
                        {{ $cliente->nombre_comercial }}
                    </option>
                @empty
                    <option value="">No hay clientes registrados</option>
                @endforelse
            </select>
        </form>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Servicio</th>
                <th>Unidad</th>
                <th>Precio Pactado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tarifas as $tarifa)
                <tr>
                    <td>{{ $tarifa->servicio->descripcion }}</td>
                    <td>{{ $tarifa->servicio->unidad }}</td>
                    <td>${{ number_format($tarifa->precio_pactado, 2) }}</td>
                    <td class="actions-cell">
                        <a class="btn btn-sm" href="{{ route('operaciones.tarifas.edit', $tarifa) }}">Editar</a>
                        <form method="POST" action="{{ route('operaciones.tarifas.destroy', $tarifa) }}"
                              onsubmit="return confirm('¿Eliminar esta tarifa?');" style="margin:0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">Este cliente aún no tiene tarifas pactadas.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
