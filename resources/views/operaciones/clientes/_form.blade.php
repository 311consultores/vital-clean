@csrf

<div class="form-group">
    <label for="nombre_comercial">Nombre Comercial *</label>
    <input type="text" id="nombre_comercial" name="nombre_comercial"
           value="{{ old('nombre_comercial', $cliente->nombre_comercial ?? '') }}" required>
    @error('nombre_comercial') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label for="razon_social">Razón Social</label>
    <input type="text" id="razon_social" name="razon_social"
           value="{{ old('razon_social', $cliente->razon_social ?? '') }}">
    @error('razon_social') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label for="rfc">RFC</label>
    <input type="text" id="rfc" name="rfc" maxlength="13"
           value="{{ old('rfc', $cliente->rfc ?? '') }}">
    @error('rfc') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label for="direccion">Dirección de Recolección</label>
    <textarea id="direccion" name="direccion" rows="2">{{ old('direccion', $cliente->direccion ?? '') }}</textarea>
    @error('direccion') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label for="telefono">Teléfono</label>
    <input type="text" id="telefono" name="telefono" maxlength="15"
           value="{{ old('telefono', $cliente->telefono ?? '') }}">
    @error('telefono') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label for="email_facturacion">Email de Facturación</label>
    <input type="email" id="email_facturacion" name="email_facturacion"
           value="{{ old('email_facturacion', $cliente->email_facturacion ?? '') }}">
    @error('email_facturacion') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label>
        <input type="checkbox" name="estatus_credito" value="1" style="width:auto;"
               {{ old('estatus_credito', $cliente->estatus_credito ?? true) ? 'checked' : '' }}>
        Crédito activo (desmarcar suspende al cliente — RN-04: bloquea nuevos folios)
    </label>
</div>
