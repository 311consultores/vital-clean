@extends('layouts.app')

@section('title', 'Vital Clean — Detalle de Orden')

@section('content')
    <div class="page-header"><h1>Detalle de Orden</h1></div>

    @php
        // RN-07: flujo estrictamente secuencial. CANCELADO es una rama
        // aparte, fuera de la secuencia normal.
        $pasos = [
            'RUTA' => 'Recolección',
            'PLANTA_RECIBIDO' => 'Recibido en Planta',
            'PROCESO' => 'En Proceso',
            'LISTO' => 'Listo',
            'ENTREGADO' => 'Entregado',
        ];
        $indiceActual = array_search($orden->estatus_orden, array_keys($pasos), true);
    @endphp

    @if ($orden->estatus_orden === 'CANCELADO')
        <div class="timeline-cancelado">❌ Este pedido fue cancelado.</div>
    @else
        <div class="timeline">
            @foreach ($pasos as $clave => $etiqueta)
                @php
                    $i = array_search($clave, array_keys($pasos), true);
                    $estadoPaso = $i < $indiceActual ? 'completado' : ($i === $indiceActual ? 'actual' : '');
                @endphp
                <div class="timeline-step {{ $estadoPaso }}">
                    <div class="timeline-line"></div>
                    <div class="timeline-circle">{{ $estadoPaso === 'completado' ? '✓' : $i + 1 }}</div>
                    <div class="timeline-label">{{ $etiqueta }}</div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="card" style="max-width:640px;">
        <p><strong>Folio:</strong> {{ $orden->folio_display }}</p>
        @if ($orden->padre)
            <p style="font-size:.85rem; color:#6b7280;">
                Subnota del folio
                <a href="{{ route('operaciones.ordenes.show', $orden->padre) }}">{{ $orden->padre->folio_display }}</a>
                (mercancía pendiente de una entrega parcial anterior).
            </p>
        @endif
        <p><strong>Cliente:</strong> {{ $orden->cliente->nombre_comercial }}</p>
        <p><strong>Vendedor:</strong> {{ $orden->vendedor->nombre_completo ?? $orden->vendedor->username }}</p>
        <p><strong>Estatus:</strong> <span class="badge badge-{{ strtolower($orden->estatus_orden) }}">{{ $orden->estatus_orden }}</span></p>
        <p><strong>Prioridad:</strong>
            @php $colorPrioridad = ['alta' => 'var(--rojo)', 'media' => 'var(--amarillo)', 'baja' => 'var(--azul-claro)'][$orden->prioridad]; @endphp
            <span style="color:{{ $colorPrioridad }}; font-weight:600;">{{ ucfirst($orden->prioridad) }}</span>
        </p>
        <p><strong>Fecha de Recolección:</strong> {{ $orden->fecha_recoleccion->format('d/m/Y H:i') }}</p>
        @if ($orden->fecha_entrega_prog)
            <p><strong>Entrega Comprometida:</strong> {{ $orden->fecha_entrega_prog->format('d/m/Y') }}</p>
        @endif
        @if ($orden->geolocalizacion)
            <p><strong>Geolocalización:</strong> {{ $orden->geolocalizacion }}</p>
        @endif

        @php $esVendedor = auth()->user()->rol === 'VENDEDOR'; @endphp

        <table class="data-table" style="margin-top:1rem;">
            <thead>
                <tr>
                    <th>Artículo</th><th>Entrada</th><th>Salida</th>
                    @unless ($esVendedor)
                        <th>Precio Aplicado</th><th>Subtotal</th>
                    @endunless
                    <th>Incidencias</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orden->detalle as $linea)
                    <tr>
                        <td>{{ $linea->servicio->descripcion }}</td>
                        <td>{{ $linea->cantidad_entrada }}</td>
                        <td>{{ $linea->cantidad_salida ?? '—' }}</td>
                        @unless ($esVendedor)
                            <td>{{ $linea->precio_aplicado !== null ? '$'.number_format($linea->precio_aplicado, 2) : 'Pendiente' }}</td>
                            <td>{{ $linea->subtotal !== null ? '$'.number_format($linea->subtotal, 2) : '—' }}</td>
                        @endunless
                        <td>
                            @if ($linea->incidencias->isNotEmpty())
                                <span class="badge" style="background:var(--rojo);">
                                    {{ $linea->incidencias->count() }} {{ $linea->incidencias->count() === 1 ? 'incidencia' : 'incidencias' }}
                                </span>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @unless ($esVendedor)
            <p style="margin-top:.75rem;"><strong>Total: {{ $orden->detalle->every(fn ($l) => $l->subtotal !== null) ? '$'.number_format($orden->detalle->sum('subtotal'), 2) : 'Pendiente de conteo en planta' }}</strong></p>
        @endunless
    </div>

    @php $incidenciasOrden = $orden->detalle->pluck('incidencias')->flatten(); @endphp
    @if ($incidenciasOrden->isNotEmpty())
        <div class="card" style="max-width:640px; margin-top:1rem;">
            <h2 style="font-size:1rem; margin-top:0;">Incidencias Reportadas</h2>
            @foreach ($orden->detalle as $linea)
                @foreach ($linea->incidencias as $incidencia)
                    <div style="border-bottom:1px solid #eee; padding:.6rem 0; font-size:.85rem; display:flex; gap:.6rem; align-items:flex-start;">
                        @if ($incidencia->foto_evidencia)
                            <a href="{{ asset('uploads/incidencias/'.$incidencia->foto_evidencia) }}" target="_blank" rel="noopener" style="flex-shrink:0;">
                                <img src="{{ asset('uploads/incidencias/'.$incidencia->foto_evidencia) }}" alt="Evidencia"
                                     style="width:64px; height:64px; object-fit:cover; border-radius:6px; border:1px solid #ddd;">
                            </a>
                        @else
                            <div style="width:64px; height:64px; flex-shrink:0; border-radius:6px; background:#f3f4f6; display:flex; align-items:center; justify-content:center; color:#9ca3af; font-size:.7rem; text-align:center;">
                                sin foto
                            </div>
                        @endif
                        <div>
                            <p style="margin:0 0 .25rem;"><strong>{{ $linea->servicio->descripcion }}</strong></p>
                            <p style="margin:0; color:#6b7280;">{{ $incidencia->comentario ?? 'Sin descripción' }}</p>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    @endif

    @if ($orden->subnotas->isNotEmpty())
        <div class="card" style="max-width:640px; margin-top:1rem;">
            <h2 style="font-size:1rem; margin-top:0;">Subnotas relacionadas</h2>
            <ul style="margin:0; padding-left:1.2rem;">
                @foreach ($orden->subnotas as $sub)
                    <li>
                        <a href="{{ route('operaciones.ordenes.show', $sub) }}">{{ $sub->folio_display }}</a>
                        — <span class="badge badge-{{ strtolower($sub->estatus_orden) }}">{{ $sub->estatus_orden }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="form-actions">
        @switch(auth()->user()->rol)
            @case('VENDEDOR')
                <a href="{{ route('vendedor.home') }}" class="btn btn-secondary">Volver al Inicio</a>
                @break
            @default
                <a href="{{ route('operaciones.dashboard') }}" class="btn btn-secondary">Volver al Monitor</a>
        @endswitch
    </div>
@endsection
