<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Nota VC-{{ str_pad($orden->folio_sistema, 4, '0', STR_PAD_LEFT) }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #1f2937; }
        .encabezado { width: 100%; margin-bottom: 4px; }
        .encabezado .icono { width: 34px; height: 34px; float: left; margin-right: 8px; }
        h1 { font-size: 20px; color: #1B1A4B; margin: 0 0 2px; }
        .subtitulo { color: #6b7280; margin: 0 0 18px; font-size: 11px; }
        .datos p { margin: 2px 0; }
        .datos strong { display: inline-block; width: 150px; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { text-align: left; padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
        th { background: #26437D; color: #fff; }
        .total { text-align: right; font-size: 13px; margin-top: 10px; }
        .estatus { display: inline-block; padding: 2px 8px; border-radius: 10px; color: #fff; font-size: 10px; }
        footer { margin-top: 30px; font-size: 9px; color: #9ca3af; }
    </style>
</head>
<body>
    @php
        // dompdf renderiza <img> con datos base64 de forma mucho más
        // confiable que SVG inline (probado: el SVG del logo no se
        // dibujaba en absoluto), así que el ícono se incrusta como PNG.
        $logoBase64 = base64_encode(file_get_contents(resource_path('images/logo-nota-pdf.png')));
    @endphp
    <div class="encabezado">
        <img class="icono" src="data:image/png;base64,{{ $logoBase64 }}" alt="Vital Clean">
        <h1>VITAL CLEAN</h1>
        <p class="subtitulo">Nota de Remisión — Sistema de Gestión Operativa</p>
    </div>
    <div style="clear:both;"></div>

    <div class="datos">
        <p><strong>Folio:</strong> VC-{{ str_pad($orden->folio_sistema, 4, '0', STR_PAD_LEFT) }} / {{ $orden->folio_fisico }}</p>
        <p><strong>Cliente:</strong> {{ $orden->cliente->nombre_comercial }}</p>
        <p><strong>Estatus:</strong> {{ $orden->estatus_orden }}</p>
        <p><strong>Fecha de recolección:</strong> {{ $orden->fecha_recoleccion?->format('d/m/Y') }}</p>
        @if ($orden->fecha_entrega_prog)
            <p><strong>Entrega comprometida:</strong> {{ $orden->fecha_entrega_prog->format('d/m/Y') }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Prenda</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orden->detalle as $linea)
                <tr>
                    <td>{{ $linea->servicio->descripcion }}</td>
                    <td>{{ $linea->cantidad_salida ?? $linea->cantidad_entrada }}</td>
                    <td>{{ $linea->precio_aplicado !== null ? '$'.number_format($linea->precio_aplicado, 2) : 'Pendiente' }}</td>
                    <td>{{ $linea->subtotal !== null ? '$'.number_format($linea->subtotal, 2) : 'Pendiente' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @php $total = $orden->detalle->sum('subtotal'); @endphp
    <p class="total">
        <strong>
            Total: {{ $orden->detalle->every(fn ($l) => $l->subtotal !== null) ? '$'.number_format($total, 2) : 'Pendiente de conteo' }}
        </strong>
    </p>

    <footer>Generado el {{ now()->format('d/m/Y H:i') }} — Lavandería Vital Clean.</footer>
</body>
</html>

