@extends('layouts.admin')
@section('title', 'Venta ' . $venta->numero_comprobante)
@section('page_title', 'Venta ' . $venta->tipo_comprobante . ' ' . $venta->numero_comprobante)

@section('content')
<div class="admin-card">
    <div class="admin-card__body" style="line-height:1.7;">
        <div><strong>Pedido:</strong> <a href="{{ route('admin.pedidos.show', $venta->pedido_id) }}">#{{ str_pad($venta->pedido_id, 6, '0', STR_PAD_LEFT) }}</a></div>
        <div><strong>Cliente:</strong> {{ $venta->pedido->cliente->nombre_razon_social ?? '—' }}</div>
        <div><strong>Comprobante:</strong> {{ $venta->tipo_comprobante }} {{ $venta->numero_comprobante }}</div>
        <div><strong>Método de pago:</strong> <span class="badge badge--info">{{ $venta->metodo_pago }}</span></div>
        <div><strong>Monto pagado:</strong> S/ {{ number_format($venta->monto_pagado, 2) }}</div>
        <div><strong>Registrado por:</strong> {{ $venta->usuario->nombre_completo ?? '—' }} el {{ \Carbon\Carbon::parse($venta->fecha_emision)->format('d/m/Y H:i') }}</div>

        <hr style="margin:16px 0; border:none; border-top:1px solid #e2e8f0;">

        <table class="admin-table">
            <thead><tr><th>Producto</th><th>Cant.</th><th>Precio</th><th>Subtotal</th></tr></thead>
            <tbody>
            @foreach($venta->pedido->detalles as $d)
                <tr><td>{{ $d->producto->nombre ?? '—' }}</td><td>{{ $d->cantidad }}</td><td>S/ {{ number_format($d->precio_unitario,2) }}</td><td>S/ {{ number_format($d->subtotal,2) }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<a href="{{ route('admin.ventas.index') }}" class="btn btn--outline" style="margin-top:16px;"><i class="fa-solid fa-arrow-left"></i> Volver</a>
@endsection
