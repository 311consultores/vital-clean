@extends('layouts.app')

@section('title', 'Vital Clean — Nota de Remisión')

@php
    $esAdmin = auth()->user()->rol === 'ADMIN';
    $esVendedor = auth()->user()->rol === 'VENDEDOR';
    $soloLectura = $orden->estatus_orden !== 'LISTO' && ! $esAdmin;
    $total = $orden->detalle->sum('subtotal');
@endphp

@section('content')
    <div class="page-header no-print">
        <div>
            <h1 style="margin-bottom:.2rem;">Nota de Remisión Final</h1>
            <p style="margin:0; color:#6b7280;">
                Folio VC-{{ str_pad($orden->folio_sistema, 4, '0', STR_PAD_LEFT) }} / {{ $orden->folio_fisico }}
            </p>
        </div>
        <span class="badge badge-{{ strtolower($orden->estatus_orden) }}">{{ $orden->estatus_orden }}</span>
    </div>

    @if ($orden->padre)
        <div class="alert alert-status no-print">
            Esta es una subnota del folio VC-{{ str_pad($orden->padre->folio_sistema, 4, '0', STR_PAD_LEFT) }}
            / {{ $orden->padre->folio_fisico }} (mercancía que quedó pendiente de una entrega parcial anterior).
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error no-print">
            @foreach ($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif

    @if (session('status'))
        <div class="alert alert-status no-print">{{ session('status') }}</div>
    @endif

    @if ($orden->estatus_orden === 'ENTREGADO')
        <div class="alert alert-status no-print">
            Este folio ya fue entregado y el ciclo quedó cerrado. Aquí abajo
            puedes reimprimir la nota o reenviarla por WhatsApp.
        </div>
    @elseif ($soloLectura)
        <div class="alert alert-error no-print">
            Este folio ya no está Listo (estatus actual: {{ $orden->estatus_orden }}).
            Solo un Administrador puede reabrirlo.
        </div>
    @endif

    <div class="card" id="remision-imprimible" style="max-width:640px;">
        <h2 style="font-size:1.1rem; margin-top:0;">Vital Clean — Nota de Remisión</h2>
        <p style="margin:.1rem 0;"><strong>Folio:</strong> VC-{{ str_pad($orden->folio_sistema, 4, '0', STR_PAD_LEFT) }} / {{ $orden->folio_fisico }}</p>
        <p style="margin:.1rem 0;"><strong>Cliente:</strong> {{ $orden->cliente->nombre_comercial }}</p>
        <p style="margin:.1rem 0;"><strong>Fecha de recolección:</strong> {{ $orden->fecha_recoleccion?->format('d/m/Y') }}</p>
        @if ($orden->fecha_entrega_prog)
            <p style="margin:.1rem 0;"><strong>Fecha de entrega comprometida:</strong> {{ $orden->fecha_entrega_prog->format('d/m/Y') }}</p>
        @endif

        <table class="data-table" style="margin-top:1rem;">
            <thead>
                <tr>
                    <th>Prenda</th>
                    <th>Cantidad</th>
                    @unless ($esVendedor)
                        <th>Precio</th>
                        <th>Subtotal</th>
                    @endunless
                    @unless ($soloLectura)
                        <th>Entregar ahora</th>
                    @endunless
                </tr>
            </thead>
            <tbody>
                @foreach ($orden->detalle as $linea)
                    @php $totalLinea = $linea->cantidad_salida ?? $linea->cantidad_entrada; @endphp
                    <tr>
                        <td>{{ $linea->servicio->descripcion }}</td>
                        <td>{{ $totalLinea }}</td>
                        @unless ($esVendedor)
                            <td>{{ $linea->precio_aplicado !== null ? '$'.number_format($linea->precio_aplicado, 2) : 'Pendiente' }}</td>
                            <td>{{ $linea->subtotal !== null ? '$'.number_format($linea->subtotal, 2) : 'Pendiente' }}</td>
                        @endunless
                        @unless ($soloLectura)
                            <td>
                                <input type="number" name="entregado[{{ $linea->id_detalle }}]"
                                       class="input-entregado" data-total="{{ $totalLinea }}" data-id="{{ $linea->id_detalle }}"
                                       min="0" max="{{ $totalLinea }}"
                                       value="{{ old('entregado.'.$linea->id_detalle, $totalLinea) }}"
                                       style="width:4.5rem;" form="form-confirmar">
                                <div class="pendiente-linea" id="pendiente-{{ $linea->id_detalle }}" style="font-size:.75rem; color:#9ca3af;"></div>
                            </td>
                        @endunless
                    </tr>
                @endforeach
            </tbody>
        </table>
        @unless ($esVendedor)
            <p style="text-align:right; font-size:1.05rem; margin-top:.75rem;">
                <strong>Total: {{ $total !== null ? '$'.number_format($total, 2) : 'Pendiente' }}</strong>
            </p>
        @endunless
    </div>

    @if ($orden->subnotas->isNotEmpty())
        <div class="card no-print" style="max-width:640px; margin-top:1rem;">
            <h2 style="font-size:1rem; margin-top:0;">Subnotas generadas por entrega parcial</h2>
            <ul style="margin:0; padding-left:1.2rem;">
                @foreach ($orden->subnotas as $sub)
                    <li>
                        <a href="{{ route('entrega.remision', $sub) }}">
                            VC-{{ str_pad($sub->folio_sistema, 4, '0', STR_PAD_LEFT) }} / {{ $sub->folio_fisico }}
                        </a>
                        — <span class="badge badge-{{ strtolower($sub->estatus_orden) }}">{{ $sub->estatus_orden }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @unless ($soloLectura)
        <div class="card no-print" style="max-width:640px; margin-top:1rem;">
            <div id="grupo-folio-subnota" class="form-group" style="display:none;">
                <label for="folio_fisico_subnota">Folio físico de la subnota</label>
                <input type="text" id="folio_fisico_subnota" name="folio_fisico_subnota" form="form-confirmar"
                       value="{{ old('folio_fisico_subnota') }}" maxlength="20" placeholder="Ej. 02150">
                @error('folio_fisico_subnota') <div class="field-error">{{ $message }}</div> @enderror
                <p style="font-size:.8rem; color:#6b7280; margin:.35rem 0 0;">
                    Redujiste la cantidad de alguna prenda: lo que falta se amparará en una
                    subnota nueva para poder facturarla por parcialidades. Captura aquí su folio físico.
                </p>
            </div>

            <h2 style="font-size:1rem; margin-top:0;">Firma de Recepción</h2>
            <p style="font-size:.85rem; color:#6b7280;"><em>El cliente firma aquí para confirmar lo que recibió.</em></p>

            <canvas id="firma-canvas" width="540" height="180"
                    style="border:1px solid #d1d5db; border-radius:.375rem; touch-action:none; width:100%; max-width:540px; background:#fff;"></canvas>
            <div style="margin-top:.5rem;">
                <button type="button" id="limpiar-firma" class="btn btn-secondary btn-sm">Limpiar Firma</button>
            </div>

            <form method="POST" action="{{ route('entrega.confirmar', $orden) }}" id="form-confirmar" style="margin-top:1rem;">
                @csrf
                <input type="hidden" name="firma" id="firma-input">
                <div class="form-actions">
                    <button type="submit" class="btn" style="background:#28a745;">Confirmar Entrega</button>
                    <button type="button" class="btn btn-secondary" onclick="window.print()">Imprimir</button>
                    <a href="{{ route('entrega.buscar') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    @else
        @if ($orden->estatus_orden === 'ENTREGADO')
            <div class="card no-print" style="max-width:640px; margin-top:1rem;">
                <div style="display:flex; gap:.6rem; flex-wrap:wrap;">
                    @if ($whatsappUrl)
                        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="btn"
                           style="background:#25D366; flex:1; box-sizing:border-box;">
                            📲 Enviar nota por WhatsApp
                        </a>
                    @else
                        <div class="alert alert-error" style="text-align:left; margin:0; flex:1;">
                            Este cliente no tiene teléfono registrado — pide a un Administrador
                            que lo agregue en Clientes para poder enviarle la nota por WhatsApp.
                        </div>
                    @endif
                    <a href="{{ $pdfUrl }}" target="_blank" rel="noopener" class="btn btn-secondary">📄 Ver PDF</a>
                </div>
            </div>
        @endif
        <div class="form-actions no-print" style="margin-top:1rem;">
            <button type="button" class="btn btn-secondary" onclick="window.print()">Imprimir</button>
            @if (auth()->user()->rol === 'VENDEDOR')
                <a href="{{ route('vendedor.home') }}" class="btn btn-secondary">Volver al Inicio</a>
            @endif
            <a href="{{ route('entrega.buscar') }}" class="btn btn-secondary">Volver a Buscar</a>
        </div>
    @endif

    <style>
        @media print {
            .no-print, header.app-header, nav.sidebar { display: none !important; }
            #remision-imprimible { border: none; box-shadow: none; }
        }
    </style>

    @unless ($soloLectura)
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
                        alert('Falta la firma de recepción.');
                        return;
                    }
                    document.getElementById('firma-input').value = canvas.toDataURL('image/png');
                });

                // Entrega parcial: si alguna línea se reduce por debajo del
                // total, muestra el campo de folio físico de la subnota y lo
                // vuelve obligatorio en el navegador (el servidor también lo
                // valida, esto es solo para avisar antes de enviar).
                var inputsEntregado = document.querySelectorAll('.input-entregado');
                var grupoSubnota = document.getElementById('grupo-folio-subnota');
                var campoFolioSubnota = document.getElementById('folio_fisico_subnota');

                function actualizarAvisoSubnota() {
                    var hayPendiente = false;
                    inputsEntregado.forEach(function (input) {
                        var total = parseInt(input.dataset.total, 10) || 0;
                        var valor = parseInt(input.value, 10);
                        if (isNaN(valor)) valor = total;
                        if (valor < total) hayPendiente = true;
                    });
                    if (grupoSubnota) grupoSubnota.style.display = hayPendiente ? 'block' : 'none';
                    if (campoFolioSubnota) {
                        if (hayPendiente) campoFolioSubnota.setAttribute('required', 'required');
                        else campoFolioSubnota.removeAttribute('required');
                    }
                }

                inputsEntregado.forEach(function (input) {
                    input.addEventListener('input', function () {
                        var total = parseInt(input.dataset.total, 10) || 0;
                        var valor = parseInt(input.value, 10);
                        if (isNaN(valor)) valor = 0;
                        if (valor > total) { valor = total; input.value = total; }
                        if (valor < 0) { valor = 0; input.value = 0; }
                        var etiqueta = document.getElementById('pendiente-' + input.dataset.id);
                        if (etiqueta) {
                            var pendiente = total - valor;
                            etiqueta.textContent = pendiente > 0 ? ('Pendiente: ' + pendiente) : '';
                        }
                        actualizarAvisoSubnota();
                    });
                });

                actualizarAvisoSubnota();
            })();
        </script>
    @endunless
@endsection
