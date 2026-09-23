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

    @php $esAdmin = auth()->user()->rol === 'ADMIN'; @endphp

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
        @if ($esAdmin)
            <div class="card"><div style="font-size:.8rem; color:#6b7280;">Pendiente por Cobrar</div><div style="font-size:1.6rem; font-weight:700; color:var(--rojo);">${{ number_format($kpis['pendiente_cobrar'] ?? 0, 2) }}</div></div>
        @endif
    </div>

    @php
        // #16: "En Proceso" = desde recolección hasta Listo; "Finalizadas"
        // = Entregado/Cancelado (los dos estatus terminales, RN-07).
        $paramsProceso = array_merge(request()->except('vista', 'page'), ['vista' => 'proceso']);
        $paramsFinalizadas = array_merge(request()->except('vista', 'page'), ['vista' => 'finalizadas']);
    @endphp
    <div style="display:flex; gap:.4rem; margin-bottom:1rem; border-bottom:1px solid #e5e7eb;">
        <a href="{{ route('operaciones.dashboard', $paramsProceso) }}"
           style="padding:.6rem 1rem; text-decoration:none; font-weight:600; font-size:.9rem; border-bottom:3px solid {{ $vista === 'proceso' ? 'var(--azul)' : 'transparent' }}; color:{{ $vista === 'proceso' ? 'var(--azul)' : '#6b7280' }};">
            En Proceso
        </a>
        <a href="{{ route('operaciones.dashboard', $paramsFinalizadas) }}"
           style="padding:.6rem 1rem; text-decoration:none; font-weight:600; font-size:.9rem; border-bottom:3px solid {{ $vista === 'finalizadas' ? 'var(--azul)' : 'transparent' }}; color:{{ $vista === 'finalizadas' ? 'var(--azul)' : '#6b7280' }};">
            Entregadas y Finalizadas
        </a>
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
                    @if ($esAdmin)
                        <th>Total ($)</th>
                    @endif
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ordenes as $orden)
                    <tr data-href="{{ route('operaciones.ordenes.show', $orden) }}">
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
                        @if ($esAdmin)
                            <td>{{ $orden->total !== null ? '$'.number_format($orden->total, 2) : 'Pendiente' }}</td>
                        @endif
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
