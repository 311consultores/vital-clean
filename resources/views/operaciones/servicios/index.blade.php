@extends('layouts.app')

@section('title', 'Vital Clean — Servicios')

@section('content')
    <div class="page-header">
        <h1>Catálogo de Servicios (Prendas)</h1>
        <a href="{{ route('operaciones.servicios.create') }}" class="btn">+ Nuevo Servicio</a>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Descripción</th>
                <th>Unidad</th>
                <th>Categoría</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($servicios as $servicio)
                <tr>
                    <td>{{ $servicio->descripcion }}</td>
                    <td>{{ $servicio->unidad }}</td>
                    <td>{{ $servicio->categoria ?? '—' }}</td>
                    <td class="actions-cell">
                        <a class="btn btn-sm" href="{{ route('operaciones.servicios.edit', $servicio) }}">Editar</a>
                        <form method="POST" action="{{ route('operaciones.servicios.destroy', $servicio) }}"
                              onsubmit="return confirm('¿Eliminar este servicio?');" style="margin:0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">Aún no hay servicios registrados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:1rem;">{{ $servicios->links() }}</div>
@endsection
