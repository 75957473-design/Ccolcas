@extends('layouts.admin')
@section('title', 'Usuarios')
@section('page_title', 'Usuarios y Roles')

@section('content')
<div style="display:flex; justify-content:space-between; gap:12px; margin-bottom:16px; flex-wrap:wrap;">
    <form method="GET" action="{{ route('admin.usuarios.index') }}" style="display:flex; gap:8px;">
        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por nombre o email" class="form-control" style="min-width:240px;">
        <select name="rol_id" class="form-control">
            <option value="">Todos los roles</option>
            @foreach($roles as $r)
                <option value="{{ $r->id }}" {{ (string) $rolId === (string) $r->id ? 'selected' : '' }}>{{ $r->nombre }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn--outline btn--sm"><i class="fa-solid fa-magnifying-glass"></i> Filtrar</button>
    </form>
    <a href="{{ route('admin.usuarios.create') }}" class="btn btn--primary"><i class="fa-solid fa-plus"></i> Nuevo usuario</a>
</div>

<div class="admin-card">
    <div class="admin-card__body" style="padding:0;">
        <table class="admin-table">
            <thead><tr><th>Usuario</th><th>Rol</th><th>Estado</th><th style="text-align:right;">Acciones</th></tr></thead>
            <tbody>
            @forelse($usuarios as $u)
                <tr>
                    <td>
                        <div style="font-weight:700;">{{ $u->nombre_completo }}</div>
                        <div style="font-size:12px; color:#64748b;">{{ $u->email }}</div>
                    </td>
                    <td><span class="badge badge--info">{{ $u->rol->nombre ?? '—' }}</span></td>
                    <td>
                        @if($u->activo) <span class="badge badge--success">Activo</span>
                        @else <span class="badge badge--neutral">Inactivo</span> @endif
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                        <a href="{{ route('admin.usuarios.edit', $u) }}" class="btn btn--ghost btn--sm"><i class="fa-solid fa-pen"></i></a>
                        <form action="{{ route('admin.usuarios.toggle', $u) }}" method="POST" style="display:inline;">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn--ghost btn--sm"><i class="fa-solid {{ $u->activo ? 'fa-eye-slash' : 'fa-eye' }}"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4"><div class="empty-state">No hay usuarios con ese criterio.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top:16px;">{{ $usuarios->links() }}</div>
@endsection
