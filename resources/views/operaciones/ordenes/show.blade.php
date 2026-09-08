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
        <p><strong>Folio Sistema:</strong> VC-{{ str_pad($orden->folio_sistema, 4, '0', STR_PAD_LEFT) }}</p>
        <p><strong>Folio Físico:</strong> {{ $orden->folio_fisico }}</p>
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

        <table class="data-table" style="margin-top:1rem;">
            <thead>
                <tr><th>Artículo</th><th>Entrada</th><th>Salida</th><th>Precio Aplicado</th><th>Subtotal</th><th>Incidencias</th></tr>
            </thead>
            <tbody>
                @foreach ($orden->detalle as $linea)
                    <tr>
                        <td>{{ $linea->servicio->descripcion }}</td>
                        <td>{{ $linea->cantidad_entrada }}</td>
                        <td>{{ $linea->cantidad_salida ?? '—' }}</td>
                        <td>{{ $linea->precio_aplicado !== null ? '$'.number_format($linea->precio_aplicado, 2) : 'Pendiente' }}</td>
                        <td>{{ $linea->subtotal !== null ? '$'.number_format($linea->subtotal, 2) : '—' }}</td>
                        <td>
                            @forelse ($linea->incidencias as $incidencia)
                                <span class="badge" style="background:var(--rojo);">{{ $incidencia->comentario ?? 'Daño' }}</span>
                            @empty
                                —
                            @endforelse
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p style="margin-top:.75rem;"><strong>Total: {{ $orden->detalle->every(fn ($l) => $l->subtotal !== null) ? '$'.number_format($orden->detalle->sum('subtotal'), 2) : 'Pendiente de conteo en planta' }}</strong></p>
    </div>

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
