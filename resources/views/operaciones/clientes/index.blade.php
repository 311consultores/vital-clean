@extends('layouts.app')

@section('title', 'Vital Clean — Clientes')

@section('content')
    <div class="page-header">
        <h1>Catálogo de Clientes</h1>
        <a href="{{ route('operaciones.clientes.create') }}" class="btn">+ Nuevo Cliente</a>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Nombre Comercial</th>
                <th>RFC</th>
                <th>Teléfono</th>
                <th>Crédito</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($clientes as $cliente)
                <tr>
                    <td>{{ $cliente->nombre_comercial }}</td>
                    <td>{{ $cliente->rfc ?? '—' }}</td>
                    <td>{{ $cliente->telefono ?? '—' }}</td>
                    <td>
                        @if ($cliente->estatus_credito)
                            <span class="badge" style="background:#28a745;">Activo</span>
                        @else
                            <span class="badge" style="background:#C0392B;">Suspendido</span>
                        @endif
                    </td>
                    <td class="actions-cell">
                        <a class="btn btn-sm" href="{{ route('operaciones.clientes.edit', $cliente) }}">Editar</a>
                        <form method="POST" action="{{ route('operaciones.clientes.destroy', $cliente) }}"
                              onsubmit="return confirm('¿Eliminar este cliente?');" style="margin:0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">Aún no hay clientes registrados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:1rem;">{{ $clientes->links() }}</div>
@endsection
