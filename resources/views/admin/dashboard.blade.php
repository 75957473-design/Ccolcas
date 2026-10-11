@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="admin-stats">
    <div class="admin-stat">
        <div class="admin-stat__label">Productos activos</div>
        <div class="admin-stat__value">{{ $stats['productos_activos'] }} <span style="font-size:14px; font-weight:600; color:#64748b;">/ {{ $stats['productos'] }}</span></div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat__label">Pedidos pendientes</div>
        <div class="admin-stat__value">{{ $stats['pedidos_pendientes'] }} <span style="font-size:14px; font-weight:600; color:#64748b;">/ {{ $stats['pedidos'] }}</span></div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat__label">Ventas registradas</div>
        <div class="admin-stat__value">{{ $stats['ventas'] }}</div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat__label">Clientes</div>
        <div class="admin-stat__value">{{ $stats['clientes'] }}</div>
    </div>
    <div class="admin-stat" style="{{ $stats['stock_critico'] > 0 ? 'border-color:#fecaca; background:#fef2f2;' : '' }}">
        <div class="admin-stat__label"><i class="fa-solid fa-triangle-exclamation"></i> Stock crítico</div>
        <div class="admin-stat__value" style="{{ $stats['stock_critico'] > 0 ? 'color:#dc2626;' : '' }}">{{ $stats['stock_critico'] }}</div>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
    <div class="admin-card">
        <div class="admin-card__header">
            <span class="admin-card__title"><i class="fa-solid fa-receipt"></i> Últimos pedidos</span>
            <a href="{{ route('admin.pedidos.index') }}" class="btn btn--ghost btn--sm">Ver todos</a>
        </div>
        <div class="admin-card__body" style="padding:0;">
            @if($ultimosPedidos->isEmpty())
                <div class="empty-state">Sin pedidos aún.</div>
            @else
                <table class="admin-table">
                    <thead><tr><th>#</th><th>Cliente</th><th>Estado</th><th>Total</th></tr></thead>
                    <tbody>
                    @foreach($ultimosPedidos as $pedido)
                        <tr>
                            <td><a href="{{ route('admin.pedidos.show', $pedido) }}">#{{ str_pad($pedido->id, 6, '0', STR_PAD_LEFT) }}</a></td>
                            <td>{{ $pedido->cliente->nombre_razon_social ?? '—' }}</td>
                            <td><span class="badge badge--pending">{{ $pedido->estado->nombre ?? 'Pendiente' }}</span></td>
                            <td>S/ {{ number_format($pedido->monto_total, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card__header">
            <span class="admin-card__title"><i class="fa-solid fa-cash-register"></i> Últimas ventas</span>
            <a href="{{ route('admin.ventas.index') }}" class="btn btn--ghost btn--sm">Ver todas</a>
        </div>
        <div class="admin-card__body" style="padding:0;">
            @if($ultimasVentas->isEmpty())
                <div class="empty-state">Sin ventas aún.</div>
            @else
                <table class="admin-table">
                    <thead><tr><th>Comprobante</th><th>Pedido</th><th>Monto</th></tr></thead>
                    <tbody>
                    @foreach($ultimasVentas as $venta)
                        <tr>
                            <td><a href="{{ route('admin.ventas.show', $venta) }}">{{ $venta->tipo_comprobante }} {{ $venta->numero_comprobante }}</a></td>
                            <td>#{{ str_pad($venta->pedido_id, 6, '0', STR_PAD_LEFT) }}</td>
                            <td>S/ {{ number_format($venta->monto_pagado, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
