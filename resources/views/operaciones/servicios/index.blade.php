@extends('layouts.app')

@section('title', 'Vital Clean — Servicios')

@section('content')
    <div class="page-header">
        <h1>Catálogo de Servicios (Prendas)</h1>
        <div style="display:flex; gap:.5rem; flex-wrap:wrap; align-items:center;">
            <form method="GET" action="{{ route('operaciones.servicios.index') }}" style="display:flex; gap:.5rem; flex-wrap:wrap;">
                <input type="text" name="buscar" placeholder="Buscar servicio..." value="{{ request('buscar') }}"
                       style="padding:.55rem .7rem; border:1px solid #d1d5db; border-radius:.375rem; min-width:220px;">
                <button type="submit" class="btn btn-sm">Buscar</button>
                @if (request('buscar'))
                    <a href="{{ route('operaciones.servicios.index') }}" class="btn btn-sm btn-secondary">Limpiar</a>
                @endif
            </form>
            <a href="{{ route('operaciones.servicios.create') }}" class="btn">+ Nuevo Servicio</a>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Descripción</th>
                <th>Unidad</th>
                <th>Categoría</th>
                <th>Color</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($servicios as $servicio)
                <tr>
                    <td>{{ $servicio->descripcion }}</td>
                    <td>{{ $servicio->unidad }}</td>
                    <td>{{ $servicio->categoria ?? '—' }}</td>
                    <td>{{ $servicio->requiere_color ? 'Sí' : '—' }}</td>
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
                <tr><td colspan="5">{{ request('buscar') ? 'No hay servicios que coincidan con la búsqueda.' : 'Aún no hay servicios registrados.' }}</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:1rem;">{{ $servicios->links() }}</div>
@endsection
