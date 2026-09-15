@extends('layouts.app')

@section('title', 'Vital Clean — Operaciones')

@section('content')
    <div class="page-header">
        <h1>Panel de Operaciones</h1>
        <form method="GET" action="{{ route('operaciones.dashboard') }}">
            <input type="text" name="buscar" placeholder="Buscar por folio físico o cliente..."
                   value="{{ request('buscar') }}"
                   style="padding:.55rem .7rem; border:1px solid #d1d5db; border-radius:.375rem; min-width:260px;">
            <button type="submit" class="btn btn-sm">Buscar</button>
            @if (request('buscar'))
                <a href="{{ route('operaciones.dashboard') }}" class="btn btn-sm btn-secondary">Limpiar</a>
            @endif
        </form>
    </div>

    @if ($pedidosNuevos > 0)
        <div class="alert alert-status" style="margin-bottom:1rem;">
            🔔 {{ $pedidosNuevos }} {{ $pedidosNuevos === 1 ? 'pedido nuevo' : 'pedidos nuevos' }} desde tu última visita.
        </div>
    @endif

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(140px, 1fr)); gap:.75rem; margin-bottom:1.25rem;">
        <div class="card"><div style="font-size:.8rem; color:#6b7280;">En Ruta</div><div style="font-size:1.6rem; font-weight:700; color:var(--amarillo);">{{ $kpis['en_ruta'] }}</div></div>
        <div class="card"><div style="font-size:.8rem; color:#6b7280;">En Auditoría</div><div style="font-size:1.6rem; font-weight:700; color:var(--azul-claro);">{{ $kpis['en_auditoria'] }}</div></div>
        <div class="card"><div style="font-size:.8rem; color:#6b7280;">En Lavado/Proceso</div><div style="font-size:1.6rem; font-weight:700; color:#8e44ad;">{{ $kpis['en_proceso'] }}</div></div>
        <div class="card"><div style="font-size:.8rem; color:#6b7280;">Listos/Por Entregar</div><div style="font-size:1.6rem; font-weight:700; color:#28a745;">{{ $kpis['listos'] }}</div></div>
        <div class="card"><div style="font-size:.8rem; color:#6b7280;">Entregados (hoy)</div><div style="font-size:1.6rem; font-weight:700; color:#1e7e34;">{{ $kpis['entregados_hoy'] }}</div></div>
    </div>

    <div class="card" style="padding:0; overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th></th>
                    <th>Folio</th>
                    <th>Cliente</th>
                    <th>Estatus</th>
                    <th>Vendedor</th>
                    <th>Total ($)</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ordenes as $orden)
                    <tr>
                        <td>
                            @php
                                $colorPrioridad = ['alta' => 'var(--rojo)', 'media' => 'var(--amarillo)', 'baja' => 'var(--azul-claro)'][$orden->prioridad];
                            @endphp
                            <span title="Prioridad {{ ucfirst($orden->prioridad) }}"
                                  style="display:inline-block; width:10px; height:10px; border-radius:50%; background:{{ $colorPrioridad }};"></span>
                        </td>
                        <td>{{ $orden->folio_display }}</td>
                        <td>{{ $orden->cliente->nombre_comercial }}</td>
                        <td><span class="badge badge-{{ strtolower($orden->estatus_orden) }}">{{ $orden->estatus_orden }}</span></td>
                        <td>{{ $orden->vendedor->nombre_completo ?? $orden->vendedor->username }}</td>
                        <td>{{ $orden->total !== null ? '$'.number_format($orden->total, 2) : 'Pendiente' }}</td>
                        <td><a class="btn btn-sm" href="{{ route('operaciones.ordenes.show', $orden) }}">Ver</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7">No hay folios que coincidan con la búsqueda.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="padding:1rem;">{{ $ordenes->links() }}</div>
    </div>
@endsection
