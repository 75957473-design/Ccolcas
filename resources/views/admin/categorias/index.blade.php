@extends('layouts.admin')
@section('title', 'Categorías')
@section('page_title', 'Categorías')

@section('content')
<div style="display:grid; grid-template-columns: 340px 1fr; gap:20px;">
    <div class="admin-card">
        <div class="admin-card__header"><span class="admin-card__title">Nueva categoría</span></div>
        <div class="admin-card__body">
            <form action="{{ route('admin.categorias.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" class="form-control" required placeholder="Detergentes">
                </div>
                <div class="form-group">
                    <label for="slug">Slug (opcional)</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug') }}" class="form-control" placeholder="detergentes">
                    <span class="form-help">Si lo dejas vacío se genera automático.</span>
                </div>
                <button type="submit" class="btn btn--primary" style="width:100%; justify-content:center;"><i class="fa-solid fa-plus"></i> Crear</button>
            </form>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card__body" style="padding:0;">
            <table class="admin-table">
                <thead><tr><th>Nombre</th><th>Slug</th><th>Productos</th><th>Estado</th><th style="text-align:right;">Acciones</th></tr></thead>
                <tbody>
                @forelse($categorias as $cat)
                    <tr>
                        <td style="font-weight:700;">{{ $cat->nombre }}</td>
                        <td><code>{{ $cat->slug }}</code></td>
                        <td>{{ $cat->productos_count }}</td>
                        <td>
                            @if($cat->activo) <span class="badge badge--success">Activa</span>
                            @else <span class="badge badge--neutral">Inactiva</span> @endif
                        </td>
                        <td style="text-align:right; white-space:nowrap;">
                            <form action="{{ route('admin.categorias.toggle', $cat) }}" method="POST" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn--ghost btn--sm"><i class="fa-solid {{ $cat->activo ? 'fa-eye-slash' : 'fa-eye' }}"></i></button>
                            </form>
                            <form action="{{ route('admin.categorias.destroy', $cat) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn--ghost btn--sm" data-confirm="¿Eliminar categoría? Solo si no tiene productos."><i class="fa-solid fa-trash" style="color:#dc2626;"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty-state">No hay categorías aún.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div style="margin-top:16px;">{{ $categorias->links() }}</div>
@endsection
