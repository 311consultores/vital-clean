@csrf

<div class="form-group">
    <label for="descripcion">Descripción *</label>
    <input type="text" id="descripcion" name="descripcion"
           value="{{ old('descripcion', $servicio->descripcion ?? '') }}" required>
    @error('descripcion') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label for="unidad">Unidad *</label>
    @php $unidadActual = old('unidad', $servicio->unidad ?? 'PZA'); @endphp
    <select id="unidad" name="unidad" required>
        <option value="PZA" {{ $unidadActual === 'PZA' ? 'selected' : '' }}>Pieza (PZA)</option>
        <option value="KG" {{ $unidadActual === 'KG' ? 'selected' : '' }}>Kilogramo (KG)</option>
    </select>
    @error('unidad') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label for="categoria">Categoría</label>
    <input type="text" id="categoria" name="categoria" maxlength="50" list="categorias-sugeridas"
           value="{{ old('categoria', $servicio->categoria ?? '') }}">
    <datalist id="categorias-sugeridas">
        <option value="Hotelería">
        <option value="Restaurante">
        <option value="Uniformes">
        <option value="Otros">
    </datalist>
    @error('categoria') <div class="field-error">{{ $message }}</div> @enderror
</div>
