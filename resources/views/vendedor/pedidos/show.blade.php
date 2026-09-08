@extends('layouts.app')

@section('title', 'Vital Clean — Detalle de Pedido')

@section('content')
    <div class="page-header"><h1>Detalle de Pedido</h1></div>

    <div class="card" style="max-width:600px;">
        <p><strong>Nota No.:</strong> VC-{{ str_pad($nota->folio_sistema, 4, '0', STR_PAD_LEFT) }}</p>
        <p><strong>Folio Físico:</strong> {{ $nota->folio_fisico }}</p>
        <p><strong>Cliente:</strong> {{ $nota->cliente->nombre_comercial }}</p>
        <p><strong>Estatus:</strong> <span class="badge badge-{{ strtolower($nota->estatus_orden) }}">{{ $nota->estatus_orden }}</span></p>
        <p><strong>Fecha de Recolección:</strong> {{ $nota->fecha_recoleccion->format('d/m/Y H:i') }}</p>
        @if ($nota->fecha_entrega_prog)
            <p><strong>Entrega Comprometida:</strong> {{ $nota->fecha_entrega_prog->format('d/m/Y') }}</p>
        @endif

        <table class="data-table" style="margin-top:1rem;">
            <thead>
                <tr><th>Artículo</th><th>Cantidad</th></tr>
            </thead>
            <tbody>
                @foreach ($nota->detalle as $linea)
                    <tr>
                        <td>{{ $linea->servicio->descripcion }}</td>
                        <td>{{ $linea->cantidad_entrada }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p style="margin-top:.75rem;"><strong>Total de Piezas: {{ $nota->detalle->sum('cantidad_entrada') }}</strong></p>
    </div>

    <div class="card" style="max-width:600px; margin-top:1rem;">
        <div style="display:flex; gap:.6rem; flex-wrap:wrap;">
            @if ($nota->estatus_orden === 'LISTO')
                <a href="{{ route('entrega.remision', $nota) }}" class="btn" style="background:#28a745;">
                    Entregar / Cerrar Pedido
                </a>
            @endif
            @if ($whatsappUrl)
                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="btn" style="background:#25D366;">
                    📲 Enviar nota por WhatsApp
                </a>
            @endif
            <a href="{{ $pdfUrl }}" target="_blank" rel="noopener" class="btn btn-secondary">📄 Ver PDF</a>
        </div>
        @if (! $whatsappUrl)
            <p style="color:#6b7280; font-size:.85rem; margin:.6rem 0 0;">
                Este cliente no tiene teléfono registrado — pide a un Administrador
                que lo agregue en Clientes para poder enviarle la nota por WhatsApp.
            </p>
        @endif
    </div>

    <div class="form-actions">
        <a href="{{ route('vendedor.pedidos.index') }}" class="btn btn-secondary">Volver al Listado</a>
        <a href="{{ route('vendedor.home') }}" class="btn btn-secondary">Volver al Inicio</a>
    </div>
@endsection
