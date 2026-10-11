<div class="form-grid">
    <div class="form-group form-group--full">
        <label>Nombre completo *</label>
        <input type="text" name="nombre_completo" value="{{ old('nombre_completo', $usuario->nombre_completo ?? '') }}" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Email *</label>
        <input type="email" name="email" value="{{ old('email', $usuario->email ?? '') }}" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Rol *</label>
        <select name="rol_id" class="form-control" required>
            @foreach($roles as $r)
                <option value="{{ $r->id }}" {{ (string) old('rol_id', $usuario->rol_id ?? '') === (string) $r->id ? 'selected' : '' }}>{{ $r->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>{{ $usuario ? 'Nueva contraseña (dejar vacío para no cambiar)' : 'Contraseña *' }}</label>
        <input type="password" name="password" class="form-control" {{ $usuario ? '' : 'required' }}>
    </div>
    <div class="form-group">
        <label>Confirmar contraseña</label>
        <input type="password" name="password_confirmation" class="form-control" {{ $usuario ? '' : 'required' }}>
    </div>
    <div class="form-group">
        <label>Estado</label>
        <select name="activo" class="form-control">
            <option value="1" {{ old('activo', $usuario->activo ?? 1) == 1 ? 'selected' : '' }}>Activo</option>
            <option value="0" {{ old('activo', $usuario->activo ?? 1) == 0 ? 'selected' : '' }}>Inactivo</option>
        </select>
    </div>
</div>

<div style="margin-top:16px; display:flex; gap:10px;">
    <button type="submit" class="btn btn--primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
    <a href="{{ route('admin.usuarios.index') }}" class="btn btn--outline">Cancelar</a>
</div>
