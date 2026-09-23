@extends('layouts.app')

@section('title', 'Vital Clean — Nuevo Cliente')

@section('content')
    <div class="page-header"><h1>Nuevo Cliente</h1></div>

    <div class="card" style="max-width:520px;">
        <form method="POST" action="{{ route('operaciones.clientes.store') }}">
            @include('operaciones.clientes._form')

            @if ($clientesConTarifas->isNotEmpty())
                <div class="form-group">
                    <label for="clonar_tarifario_de">Clonar tarifario de otro cliente (opcional)</label>
                    <select id="clonar_tarifario_de" name="clonar_tarifario_de">
                        <option value="">— No clonar, capturar tarifario después —</option>
                        @foreach ($clientesConTarifas as $c)
                            <option value="{{ $c->id_cliente }}" {{ old('clonar_tarifario_de') == $c->id_cliente ? 'selected' : '' }}>
                                {{ $c->nombre_comercial }}
                            </option>
                        @endforeach
                    </select>
                    <p style="color:#6b7280; font-size:.8rem; margin:.35rem 0 0;">
                        Copia los mismos servicios y precios pactados de ese cliente al nuevo. Podrás editarlos después en Tarifas.
                    </p>
                    @error('clonar_tarifario_de') <div class="field-error">{{ $message }}</div> @enderror
                </div>
            @endif

            <div class="form-actions">
                <button type="submit" class="btn">Guardar</button>
                <a href="{{ route('operaciones.clientes.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
