@extends('layouts.app')

@section('title', 'Vital Clean — Mis Pedidos')

@section('content')
    <div class="page-header">
        <h1>Mis Pedidos</h1>
        <span style="display:flex; gap:.5rem;">
            <a href="{{ route('vendedor.recoleccion.create') }}" class="btn">+ Nuevo Pedido</a>
            <a href="{{ route('vendedor.home') }}" class="btn btn-secondary">Volver al Inicio</a>
        </span>
    </div>

    <form method="GET" action="{{ route('vendedor.pedidos.index') }}" style="margin-bottom:1rem;">
        <input type="text" name="buscar" placeholder="Buscar por folio o cliente..." value="{{ request('buscar') }}"
               style="padding:.55rem .7rem; border:1px solid #d1d5db; border-radius:.375rem; max-width:280px;">
        <button type="submit" class="btn btn-sm">Buscar</button>
    </form>

    <div class="card" style="padding:0; overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Cliente</th>
                    <th>Estatus</th>
                    <th>Fecha</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pedidos as $pedido)
                    <tr data-href="{{ route('vendedor.pedidos.show', $pedido) }}">
                        <td>{{ $pedido->folio_display }}</td>
                        <td>{{ $pedido->cliente->nombre_comercial }}</td>
                        <td><span class="badge badge-{{ strtolower($pedido->estatus_orden) }}">{{ $pedido->estatus_orden }}</span></td>
                        <td>{{ $pedido->fecha_recoleccion->horaLocal()->format('d/m/Y H:i') }}</td>
                        <td><a class="btn btn-sm" href="{{ route('vendedor.pedidos.show', $pedido) }}">Ver</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5">Aún no tienes pedidos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:1rem;">{{ $pedidos->links() }}</div>
@endsection
