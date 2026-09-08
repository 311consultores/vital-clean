@extends('layouts.app')

@section('title', 'Vital Clean — Pedido Creado')

@section('content')
    <div class="card" style="max-width:480px; text-align:center;">
        <div style="font-size:2.5rem; color:#28a745;">✓</div>
        <h1 style="color:#28a745;">¡Pedido creado exitosamente!</h1>
        <p><strong>Cliente:</strong> {{ $nota->cliente->nombre_comercial }}</p>
        <p><strong>Folio Sistema:</strong> VC-{{ str_pad($nota->folio_sistema, 4, '0', STR_PAD_LEFT) }}</p>
        <p><strong>Folio Físico:</strong> {{ $nota->folio_fisico }}</p>
        <p><strong>Total de piezas:</strong> {{ $nota->detalle->sum('cantidad_entrada') }}</p>

        @if ($whatsappUrl)
            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="btn"
               style="background:#25D366; width:100%; margin-bottom:.75rem; box-sizing:border-box;">
                📲 Enviar nota por WhatsApp
            </a>
        @else
            <div class="alert alert-error" style="text-align:left;">
                Este cliente no tiene teléfono registrado — pide a un Administrador
                que lo agregue en Clientes para poder enviarle la nota por WhatsApp.
            </div>
        @endif

        <div class="form-actions" style="justify-content:center;">
            <a href="{{ $pdfUrl }}" target="_blank" rel="noopener" class="btn btn-secondary">📄 Ver PDF</a>
            <a href="{{ route('vendedor.pedidos.show', $nota) }}" class="btn">Ver Detalle</a>
            <a href="{{ route('vendedor.recoleccion.create') }}" class="btn btn-secondary">Nuevo Pedido</a>
            <a href="{{ route('vendedor.home') }}" class="btn btn-secondary">Volver al Inicio</a>
        </div>
    </div>
@endsection
