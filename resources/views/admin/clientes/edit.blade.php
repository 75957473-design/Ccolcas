@extends('layouts.admin')
@section('title', 'Editar Cliente')
@section('page_title', 'Editar Cliente')

@section('content')
<div class="admin-card">
    <div class="admin-card__body">
        <form action="{{ route('admin.clientes.update', $cliente) }}" method="POST">
            @csrf @method('PUT')
            <div class="form-grid">
                <div class="form-group form-group--full">
                    <label>Nombre / Razón social *</label>
                    <input type="text" name="nombre_razon_social" value="{{ old('nombre_razon_social', $cliente->nombre_razon_social) }}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Tipo de documento *</label>
                    <select name="tipo_documento" class="form-control" required>
                        <option value="DNI" {{ old('tipo_documento', $cliente->tipo_documento) === 'DNI' ? 'selected' : '' }}>DNI</option>
                        <option value="RUC" {{ old('tipo_documento', $cliente->tipo_documento) === 'RUC' ? 'selected' : '' }}>RUC</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Número de documento *</label>
                    <input type="text" name="numero_documento" value="{{ old('numero_documento', $cliente->numero_documento) }}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono', $cliente->telefono) }}" class="form-control">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email', $cliente->email) }}" class="form-control">
                </div>
                <div class="form-group">
                    <label>Persona que recibe</label>
                    <input type="text" name="persona_recepcion" value="{{ old('persona_recepcion', $cliente->persona_recepcion) }}" class="form-control">
                </div>
                <div class="form-group form-group--full">
                    <label>Dirección de entrega</label>
                    <textarea name="direccion_entrega" rows="2" class="form-control">{{ old('direccion_entrega', $cliente->direccion_entrega) }}</textarea>
                </div>
            </div>
            <div style="margin-top:16px; display:flex; gap:10px;">
                <button type="submit" class="btn btn--primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
                <a href="{{ route('admin.clientes.index') }}" class="btn btn--outline">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
