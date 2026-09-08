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

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(140px, 1fr)); gap:.75rem; margin-bottom:1.25rem;">
        <div class="card"><div style="font-size:.8rem; color:#6b7280;">En Ruta</div><div style="font-size:1.6rem; font-weight:700; color:var(--amarillo);">{{ $kpis['en_ruta'] }}</div></div>
        <div class="card"><div style="font-size:.8rem; color:#6b7280;">En Auditoría</div><div style="font-size:1.6rem; font-weight:700; color:var(--azul-claro);">{{ $kpis['en_auditoria'] }}</div></div>
        <div class="card"><div style="font-size:.8rem; color:#6b7280;">En Lavado/Proceso</div><div style="font-size:1.6rem; font-weight:700; color:#8e44ad;">{{ $kpis['en_proceso'] }}</div></div>
        <div class="card"><div style="font-size:.8rem; color:#6b7280;">Listos/Por Entregar</div><div style="font-size:1.6rem; font-weight:700; color:#28a745;">{{ $kpis['listos'] }}</div></div>
        <div class="card"><div style="font-size:.8rem; color:#6b7280;">Entregados (hoy)</div><div style="font-size:1.6rem; font-weight:700; color:#1e7e34;">{{ $kpis['entregados_hoy'] }}</div></div>
    </div>

    <div style="display:grid; grid-template-columns:2fr 1fr; gap:1rem; align-items:start;">
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
                            <td>VC-{{ str_pad($orden->folio_sistema, 4, '0', STR_PAD_LEFT) }} / {{ $orden->folio_fisico }}</td>
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

        <div class="card">
            <h2 style="font-size:1rem; margin-top:0;">Alertas de Calidad</h2>
            @forelse ($alertasCalidad as $incidencia)
                <div style="border-bottom:1px solid #eee; padding:.6rem 0; font-size:.85rem; display:flex; gap:.6rem; align-items:flex-start;">
                    @if ($incidencia->foto_evidencia)
                        <a href="{{ asset('uploads/incidencias/'.$incidencia->foto_evidencia) }}" target="_blank" style="flex-shrink:0;">
                            <img src="{{ asset('uploads/incidencias/'.$incidencia->foto_evidencia) }}" alt="Evidencia"
                                 style="width:48px; height:48px; object-fit:cover; border-radius:4px; border:1px solid #ddd;">
                        </a>
                    @else
                        <div style="width:48px; height:48px; flex-shrink:0; border-radius:4px; background:#f3f4f6; display:flex; align-items:center; justify-content:center; color:#9ca3af; font-size:.7rem; text-align:center;">
                            sin foto
                        </div>
                    @endif
                    <div>
                        <p style="margin:0 0 .25rem;">
                            <strong>Folio:</strong>
                            VC-{{ str_pad($incidencia->detalle->notaRemision->folio_sistema, 4, '0', STR_PAD_LEFT) }}
                            — {{ $incidencia->detalle->servicio->descripcion }}
                        </p>
                        <p style="margin:0; color:#6b7280;">{{ $incidencia->comentario ?? 'Sin descripción' }}</p>
                    </div>
                </div>
            @empty
                <p style="color:#6b7280; font-size:.85rem;">Sin incidencias reportadas.</p>
            @endforelse
        </div>
    </div>
@endsection
