@extends('layouts.admin')
@section('title', 'Productos')
@section('page_title', 'Gestión de Productos')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; gap:12px; flex-wrap:wrap;">
    <form method="GET" action="{{ route('admin.productos.index') }}" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por nombre o código" class="form-control" style="min-width:240px;">
        <select name="categoria_id" class="form-control">
            <option value="">Todas las categorías</option>
            @foreach($categorias as $cat)
                <option value="{{ $cat->id }}" {{ (string) $categoriaId === (string) $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
            @endforeach
        </select>
        <select name="estado" class="form-control">
            <option value="">Todos</option>
            <option value="activos" {{ $estado === 'activos' ? 'selected' : '' }}>Activos</option>
            <option value="inactivos" {{ $estado === 'inactivos' ? 'selected' : '' }}>Inactivos</option>
        </select>
        <button type="submit" class="btn btn--outline btn--sm"><i class="fa-solid fa-magnifying-glass"></i> Filtrar</button>
    </form>
    <a href="{{ route('admin.productos.create') }}" class="btn btn--primary"><i class="fa-solid fa-plus"></i> Nuevo producto</a>
</div>

<div class="admin-card">
    <div class="admin-card__body" style="padding:0;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <th style="text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
            @forelse($productos as $producto)
                <tr class="{{ $producto->enStockCritico() ? 'row--danger' : '' }}">
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <img src="{{ $producto->url_imagen }}" alt="" class="thumb">
                            <div>
                                <div style="font-weight:700;">{{ $producto->nombre }}</div>
                                <div style="font-size:12px; color:#64748b;">{{ $producto->codigo ?? '—' }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $producto->categoria->nombre ?? '—' }}</td>
                    <td>
                        <div style="font-weight:700;">S/ {{ number_format($producto->precio_venta, 2) }}</div>
                        <div style="font-size:11px; color:#64748b;">Compra S/ {{ number_format($producto->costo_compra,2) }} + Flete S/ {{ number_format($producto->costo_flete,2) }} ({{ $producto->margen_ganancia }}%)</div>
                    </td>
                    <td>
                        {{ $producto->stockTotal() }} unid.
                        @if($producto->enStockCritico()) <span class="badge badge--danger" style="margin-left:6px;">Crítico</span> @endif
                    </td>
                    <td>
                        @if($producto->activo) <span class="badge badge--success">Activo</span>
                        @else <span class="badge badge--neutral">Inactivo</span> @endif
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                        <a href="{{ route('admin.productos.edit', $producto) }}" class="btn btn--ghost btn--sm"><i class="fa-solid fa-pen"></i></a>
                        <form action="{{ route('admin.productos.toggle', $producto) }}" method="POST" style="display:inline;">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn--ghost btn--sm" title="Activar/Desactivar">
                                <i class="fa-solid {{ $producto->activo ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty-state">No hay productos con ese criterio.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top:16px;">{{ $productos->links() }}</div>
@endsection
