@extends('layouts.admin')
@section('title', 'Ventas')
@section('page_title', 'Gestión de Ventas')

@section('content')
<div style="display:flex; justify-content:space-between; gap:12px; margin-bottom:16px; flex-wrap:wrap;">
    <form method="GET" action="{{ route('admin.ventas.index') }}" style="display:flex; gap:8px;">
        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar comprobante o tipo" class="form-control" style="min-width:260px;">
        <button type="submit" class="btn btn--outline btn--sm"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button>
    </form>
    <a href="{{ route('admin.ventas.create') }}" class="btn btn--primary"><i class="fa-solid fa-plus"></i> Registrar venta</a>
</div>

<div class="admin-card">
    <div class="admin-card__body" style="padding:0;">
        <table class="admin-table">
            <thead><tr><th>Comprobante</th><th>Pedido</th><th>Cliente</th><th>Método</th><th>Monto</th><th>Fecha</th><th></th></tr></thead>
            <tbody>
            @forelse($ventas as $venta)
                <tr>
                    <td><strong>{{ $venta->tipo_comprobante }}</strong> {{ $venta->numero_comprobante }}</td>
                    <td><a href="{{ route('admin.pedidos.show', $venta->pedido_id) }}">#{{ str_pad($venta->pedido_id, 6, '0', STR_PAD_LEFT) }}</a></td>
                    <td>{{ $venta->pedido->cliente->nombre_razon_social ?? '—' }}</td>
                    <td><span class="badge badge--info">{{ $venta->metodo_pago }}</span></td>
                    <td style="font-weight:700;">S/ {{ number_format($venta->monto_pagado, 2) }}</td>
                    <td style="font-size:13px;">{{ \Carbon\Carbon::parse($venta->fecha_emision)->format('d/m/Y H:i') }}</td>
                    <td><a href="{{ route('admin.ventas.show', $venta) }}" class="btn btn--ghost btn--sm"><i class="fa-solid fa-eye"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="empty-state">No hay ventas registradas.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top:16px;">{{ $ventas->links() }}</div>
@endsection
