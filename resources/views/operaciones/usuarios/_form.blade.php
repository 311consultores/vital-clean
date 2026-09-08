@csrf

<div class="form-group">
    <label for="username">Nombre de Usuario *</label>
    <input type="text" id="username" name="username" maxlength="50"
           value="{{ old('username', $usuario->username ?? '') }}" required>
    @error('username') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label for="nombre_completo">Nombre Completo</label>
    <input type="text" id="nombre_completo" name="nombre_completo"
           value="{{ old('nombre_completo', $usuario->nombre_completo ?? '') }}">
    @error('nombre_completo') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label for="email">Email</label>
    <input type="email" id="email" name="email"
           value="{{ old('email', $usuario->email ?? '') }}">
    @error('email') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label for="rol">Rol *</label>
    @php $esUsuarioActual = isset($usuario) && $usuario->id_usuario === auth()->user()->id_usuario; @endphp
    <select id="rol" name="rol" required {{ $esUsuarioActual ? 'disabled' : '' }}>
        <option value="">— Selecciona un rol —</option>
        @foreach (['VENDEDOR', 'OPERADOR', 'ADMIN'] as $rol)
            <option value="{{ $rol }}" {{ old('rol', $usuario->rol ?? '') === $rol ? 'selected' : '' }}>{{ $rol }}</option>
        @endforeach
    </select>
    @if ($esUsuarioActual)
        <input type="hidden" name="rol" value="{{ $usuario->rol }}">
        <p style="color:#6b7280; font-size:.8rem; margin:.3rem 0 0;">No puedes cambiar tu propio rol.</p>
    @endif
    @error('rol') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label for="password">Contraseña {{ isset($usuario) ? '' : '*' }}</label>
    <input type="password" id="password" name="password" autocomplete="new-password"
           placeholder="{{ isset($usuario) ? 'Dejar en blanco para no cambiarla' : '' }}" {{ isset($usuario) ? '' : 'required' }}>
    @error('password') <div class="field-error">{{ $message }}</div> @enderror
</div>

@if (isset($usuario) && $esUsuarioActual)
    <input type="hidden" name="activo" value="1">
    <p style="color:#6b7280; font-size:.85rem;">No puedes desactivar tu propia cuenta.</p>
@else
    <div class="form-group">
        <label>
            <input type="checkbox" name="activo" value="1" style="width:auto;"
                   {{ old('activo', $usuario->activo ?? true) ? 'checked' : '' }}>
            Usuario activo (desmarcar le impide iniciar sesión)
        </label>
        @error('activo') <div class="field-error">{{ $message }}</div> @enderror
    </div>
@endif
