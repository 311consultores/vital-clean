@extends('layouts.app')

@section('title', 'Vital Clean — Cierre de Producción')

@php
    $esAdmin = auth()->user()->rol === 'ADMIN';
    $soloLectura = $orden->estatus_orden !== 'PROCESO' && ! $esAdmin;
@endphp

@section('content')
    <div class="page-header">
        <div>
            <h1 style="margin-bottom:.2rem;">{{ $orden->cliente->nombre_comercial }}</h1>
            <p style="margin:0; color:#6b7280;">
                Folio {{ $orden->folio_display }}
            </p>
        </div>
        <span class="badge badge-{{ strtolower($orden->estatus_orden) }}">{{ $orden->estatus_orden }}</span>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            @foreach ($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif

    @if ($soloLectura)
        <div class="alert alert-error">
            Este folio ya no está en Proceso (estatus actual: {{ $orden->estatus_orden }}).
            Solo un Administrador puede reabrirlo.
        </div>
    @elseif ($orden->estatus_orden !== 'PROCESO' && $esAdmin)
        <div class="alert alert-status">
            Este folio ya avanzó a {{ $orden->estatus_orden }}. Estás editándolo como Administrador.
        </div>
    @endif

    <form method="POST" action="{{ route('produccion.guardar', $orden) }}" enctype="multipart/form-data" id="form-produccion">
        @csrf
        <div class="card" style="padding:0; overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Prenda</th>
                        <th>Entrada (Planta)</th>
                        <th style="width:110px;">Salida (Producción)</th>
                        <th>Incidencia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orden->detalle as $linea)
                        <tr>
                            <td>{{ $linea->servicio->descripcion }} <span style="color:#9ca3af;">({{ $linea->servicio->unidad }})</span></td>
                            <td>{{ $linea->cantidad_entrada }}</td>
                            <td>
                                @if ($soloLectura)
                                    {{ $linea->cantidad_salida ?? '—' }}
                                @else
                                    <input type="number" min="0" step="1" name="salidas[{{ $linea->id_detalle }}]"
                                           value="{{ old('salidas.'.$linea->id_detalle, $linea->cantidad_salida ?? $linea->cantidad_entrada) }}" style="width:75px;">
                                @endif
                            </td>
                            <td>
                                @foreach ($linea->incidencias as $incidencia)
                                    <div style="margin-bottom:.3rem;">
                                        <span class="badge" style="background:var(--rojo);">{{ $incidencia->comentario ?? 'Daño' }}</span>
                                        @if ($incidencia->foto_evidencia)
                                            <a href="{{ asset('uploads/incidencias/'.$incidencia->foto_evidencia) }}" target="_blank" style="font-size:.8rem;">ver foto</a>
                                        @endif
                                    </div>
                                @endforeach

                                @unless ($soloLectura)
                                    @php $tieneErrorEstaLinea = $errors->has('foto.'.$linea->id_detalle) || old('dano.'.$linea->id_detalle) || old('comentario_dano.'.$linea->id_detalle); @endphp
                                    <details @if ($tieneErrorEstaLinea) open @endif>
                                        <summary style="cursor:pointer; font-size:.85rem; color:var(--azul);">+ Reportar incidencia</summary>
                                        <div style="margin-top:.4rem; display:flex; flex-direction:column; gap:.3rem; max-width:220px;">
                                            <select name="dano[{{ $linea->id_detalle }}]">
                                                <option value="">— Tipo —</option>
                                                @foreach (['Quemado', 'Mancha', 'Roto', 'Faltante', 'Otro'] as $tipo)
                                                    <option value="{{ $tipo }}" @selected(old('dano.'.$linea->id_detalle) === $tipo)>{{ $tipo }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" name="comentario_dano[{{ $linea->id_detalle }}]" placeholder="Nota (opcional)" maxlength="255"
                                                   value="{{ old('comentario_dano.'.$linea->id_detalle) }}">
                                            <input type="file" name="foto[{{ $linea->id_detalle }}]" accept="image/*" capture="environment" class="foto-incidencia">
                                            @error('foto.'.$linea->id_detalle)
                                                <div class="field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </details>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p style="font-size:.8rem; color:#6b7280; margin-top:.6rem;">
            Si la salida queda por debajo de la entrada y no capturas un motivo,
            el sistema registra automáticamente una incidencia de tipo "Faltante".
        </p>

        @unless ($soloLectura)
            <div class="form-actions" style="margin-top:1rem;">
                <button type="submit" class="btn" style="background:#28a745; flex:1; padding:.9rem; font-size:1.05rem;">
                    Marcar como Listo
                </button>
                <a href="{{ route('produccion.buscar') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        @else
            <div class="form-actions" style="margin-top:1rem;">
                <a href="{{ route('produccion.buscar') }}" class="btn btn-secondary">Volver a Buscar</a>
            </div>
        @endif
    </form>

    @unless ($soloLectura)
        @include('partials.compresor-fotos')
    @endunless
@endsection
