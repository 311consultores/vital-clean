@extends('layouts.app')

@section('title', 'Vital Clean — Resumen del Pedido')

@section('content')
    <div class="page-header"><h1>Resumen del Pedido</h1></div>

    <div class="card" style="max-width:600px; margin-bottom:1rem;">
        <p><strong>Cliente:</strong> {{ $cliente->nombre_comercial }}</p>
        <p><strong>Folio Físico:</strong> {{ $folioFisico }}</p>

        <table class="data-table">
            <thead>
                <tr><th>Prenda</th><th>Cantidad</th></tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item['servicio']->descripcion }}</td>
                        <td>{{ $item['cantidad'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p style="margin-top:.75rem;"><strong>Total de piezas: {{ $totalPiezas }}</strong></p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            @foreach ($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif

    <div class="card" style="max-width:600px;">
        <h2 style="font-size:1rem; margin-top:0;">Firma de Confirmación de Pedido</h2>
        <p style="font-size:.85rem; color:#6b7280;"><em>El responsable del establecimiento firma aquí con el dedo o el mouse para aprobar.</em></p>

        <canvas id="firma-canvas" width="540" height="180"
                style="border:1px solid #d1d5db; border-radius:.375rem; touch-action:none; width:100%; max-width:540px; background:#fff;"></canvas>
        <div style="margin-top:.5rem;">
            <button type="button" id="limpiar-firma" class="btn btn-secondary btn-sm">Limpiar Firma</button>
        </div>

        <form method="POST" action="{{ route('vendedor.recoleccion.confirmar') }}" id="form-confirmar" style="margin-top:1rem;">
            @csrf
            <input type="hidden" name="firma" id="firma-input">
            <input type="hidden" name="geolocalizacion" id="geolocalizacion-input">
            <div class="form-actions">
                <button type="submit" class="btn" style="background:#28a745;">Aprobado</button>
                <a href="{{ route('vendedor.recoleccion.create') }}" class="btn btn-secondary">Regresar</a>
            </div>
        </form>
    </div>

    <script>
        (function () {
            var canvas = document.getElementById('firma-canvas');
            var ctx = canvas.getContext('2d');
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#26437D';
            var dibujando = false;
            var trazado = false;

            function posicion(evento) {
                var rect = canvas.getBoundingClientRect();
                var escalaX = canvas.width / rect.width;
                var escalaY = canvas.height / rect.height;
                var punto = evento.touches ? evento.touches[0] : evento;
                return {
                    x: (punto.clientX - rect.left) * escalaX,
                    y: (punto.clientY - rect.top) * escalaY,
                };
            }

            function iniciar(e) {
                dibujando = true;
                trazado = true;
                var p = posicion(e);
                ctx.beginPath();
                ctx.moveTo(p.x, p.y);
                e.preventDefault();
            }

            function mover(e) {
                if (!dibujando) return;
                var p = posicion(e);
                ctx.lineTo(p.x, p.y);
                ctx.stroke();
                e.preventDefault();
            }

            function terminar() { dibujando = false; }

            canvas.addEventListener('mousedown', iniciar);
            canvas.addEventListener('mousemove', mover);
            window.addEventListener('mouseup', terminar);
            canvas.addEventListener('touchstart', iniciar, { passive: false });
            canvas.addEventListener('touchmove', mover, { passive: false });
            canvas.addEventListener('touchend', terminar);

            document.getElementById('limpiar-firma').addEventListener('click', function () {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                trazado = false;
            });

            document.getElementById('form-confirmar').addEventListener('submit', function (e) {
                if (!trazado) {
                    e.preventDefault();
                    alert('Falta la firma del cliente.');
                    return;
                }
                document.getElementById('firma-input').value = canvas.toDataURL('image/png');
            });

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (pos) {
                    document.getElementById('geolocalizacion-input').value = pos.coords.latitude + ',' + pos.coords.longitude;
                }, function () { /* sin permiso; se envía vacío */ }, { timeout: 4000 });
            }
        })();
    </script>
@endsection
