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

<div class="form-group">
    <label style="display:flex; align-items:center; gap:.5rem; font-weight:600;">
        <input type="checkbox" name="requiere_color" value="1" style="width:auto;"
               @checked(old('requiere_color', $servicio->requiere_color ?? false))>
        Requiere clasificarse por color
    </label>
    <p style="margin:.3rem 0 0; font-size:.8rem; color:#6b7280;">
        Actívalo si esta prenda existe en varias subdivisiones de color (ej. blancos vs. de color) —
        al levantar un pedido, el Vendedor podrá anotar el color de esta prenda.
    </p>
</div>
