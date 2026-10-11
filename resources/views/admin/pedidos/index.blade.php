@extends('layouts.admin')
@section('title', 'Pedidos')
@section('page_title', 'Gestión de Pedidos')

@section('content')
<form method="GET" action="{{ route('admin.pedidos.index') }}" style="display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap;">
    <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por #ID, cliente o dirección" class="form-control" style="min-width:260px;">
    <select name="estado_id" class="form-control">
        <option value="">Todos los estados</option>
        @foreach($estados as $e)
            <option value="{{ $e->id }}" {{ (string) $estadoId === (string) $e->id ? 'selected' : '' }}>{{ $e->nombre }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn--outline btn--sm"><i class="fa-solid fa-magnifying-glass"></i> Filtrar</button>
</form>

<div class="admin-card">
    <div class="admin-card__body" style="padding:0;">
        <table class="admin-table">
            <thead><tr><th># Pedido</th><th>Cliente</th><th>Fecha</th><th>Estado</th><th>Total</th><th>Comprobante</th><th></th></tr></thead>
            <tbody>
            @forelse($pedidos as $pedido)
                <tr>
                    <td><a href="{{ route('admin.pedidos.show', $pedido) }}" style="font-weight:800;">#{{ str_pad($pedido->id, 6, '0', STR_PAD_LEFT) }}</a></td>
                    <td>
                        <div style="font-weight:600;">{{ $pedido->cliente->nombre_razon_social ?? '—' }}</div>
                        <div style="font-size:12px; color:#64748b;">{{ $pedido->cliente->numero_documento ?? '' }}</div>
                    </td>
                    <td style="font-size:13px;">{{ \Carbon\Carbon::parse($pedido->fecha_pedido)->format('d/m/Y H:i') }}</td>
                    <td><span class="badge badge--pending">{{ $pedido->estado->nombre ?? 'Pendiente' }}</span></td>
                    <td style="font-weight:700;">S/ {{ number_format($pedido->monto_total, 2) }}</td>
                    <td>{{ $pedido->tipo_comprobante_solicitado }}</td>
                    <td style="text-align:right;">
                        <a href="{{ route('admin.pedidos.show', $pedido) }}" class="btn btn--ghost btn--sm"><i class="fa-solid fa-eye"></i> Ver</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="empty-state">No hay pedidos con ese criterio.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top:16px;">{{ $pedidos->links() }}</div>
@endsection
