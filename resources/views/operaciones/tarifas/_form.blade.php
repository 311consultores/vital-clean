@csrf

<div class="form-group">
    <label for="id_cliente">Cliente *</label>
    @php $clienteActual = old('id_cliente', $tarifa->id_cliente ?? ''); @endphp
    <select id="id_cliente" name="id_cliente" required>
        <option value="">— Selecciona —</option>
        @foreach ($clientes as $cliente)
            <option value="{{ $cliente->id_cliente }}" {{ (string) $clienteActual === (string) $cliente->id_cliente ? 'selected' : '' }}>
                {{ $cliente->nombre_comercial }}
            </option>
        @endforeach
    </select>
    @error('id_cliente') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label for="id_servicio">Servicio *</label>
    @php $servicioActual = old('id_servicio', $tarifa->id_servicio ?? ''); @endphp
    <select id="id_servicio" name="id_servicio" required>
        <option value="">— Selecciona —</option>
        @foreach ($servicios as $servicio)
            <option value="{{ $servicio->id_servicio }}" {{ (string) $servicioActual === (string) $servicio->id_servicio ? 'selected' : '' }}>
                {{ $servicio->descripcion }} ({{ $servicio->unidad }})
            </option>
        @endforeach
    </select>
    @error('id_servicio') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label for="precio_pactado">Precio Pactado (MXN) *</label>
    <input type="number" id="precio_pactado" name="precio_pactado" step="0.01" min="0"
           value="{{ old('precio_pactado', $tarifa->precio_pactado ?? '') }}" required>
    @error('precio_pactado') <div class="field-error">{{ $message }}</div> @enderror
</div>
