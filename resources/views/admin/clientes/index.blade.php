@extends('layouts.admin')
@section('title', 'Clientes')
@section('page_title', 'Gestión de Clientes')

@section('content')
<form method="GET" action="{{ route('admin.clientes.index') }}" style="display:flex; gap:8px; margin-bottom:16px;">
    <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por nombre, documento, email o teléfono" class="form-control" style="min-width:320px;">
    <button type="submit" class="btn btn--outline btn--sm"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button>
</form>

<div class="admin-card">
    <div class="admin-card__body" style="padding:0;">
        <table class="admin-table">
            <thead><tr><th>Cliente</th><th>Documento</th><th>Contacto</th><th>Entrega</th><th>Pedidos</th><th></th></tr></thead>
            <tbody>
            @forelse($clientes as $c)
                <tr>
                    <td>
                        <div style="font-weight:700;">{{ $c->nombre_razon_social }}</div>
                        <div style="font-size:12px; color:#64748b;">{{ $c->usuario->email ?? $c->email ?? '—' }}</div>
                    </td>
                    <td><span class="badge badge--neutral">{{ $c->tipo_documento }}</span> {{ $c->numero_documento }}</td>
                    <td style="font-size:13px;">{{ $c->telefono ?? '—' }}<div style="color:#64748b;">{{ $c->persona_recepcion ?? '' }}</div></td>
                    <td style="font-size:13px; max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $c->direccion_entrega ?? '—' }}</td>
                    <td><span class="badge badge--info">{{ $c->pedidos_count }}</span></td>
                    <td style="text-align:right; white-space:nowrap;">
                        <a href="{{ route('admin.clientes.show', $c) }}" class="btn btn--ghost btn--sm"><i class="fa-solid fa-eye"></i></a>
                        <a href="{{ route('admin.clientes.edit', $c) }}" class="btn btn--ghost btn--sm"><i class="fa-solid fa-pen"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty-state">No hay clientes con ese criterio.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top:16px;">{{ $clientes->links() }}</div>
@endsection
