@extends('layouts.admin')
@section('title', 'Registrar Venta')
@section('page_title', 'Registrar Venta')

@section('content')
<div style="display:grid; grid-template-columns: 1fr 380px; gap:20px;">
    <div class="admin-card">
        <div class="admin-card__header"><span class="admin-card__title">Datos de la venta</span></div>
        <div class="admin-card__body">
            <form action="{{ route('admin.ventas.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>Pedido *</label>
                    <select name="pedido_id" class="form-control" required>
                        <option value="">Seleccionar pedido</option>
                        @foreach($pedidosPendientes as $p)
                            <option value="{{ $p->id }}" {{ (string) old('pedido_id', $pedido?->id ?? '') === (string) $p->id ? 'selected' : '' }}>
                                #{{ str_pad($p->id, 6, '0', STR_PAD_LEFT) }} — {{ $p->cliente->nombre_razon_social ?? '—' }} — S/ {{ number_format($p->monto_total,2) }} ({{ $p->estado->nombre ?? '' }})
                            </option>
                        @endforeach
                    </select>
                    <span class="form-help">Solo se listan pedidos sin venta. Al registrar se descontará stock.</span>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Tipo de comprobante *</label>
                        <select name="tipo_comprobante" class="form-control" required>
                            <option value="Boleta" {{ old('tipo_comprobante') === 'Boleta' ? 'selected' : '' }}>Boleta</option>
                            <option value="Factura" {{ old('tipo_comprobante') === 'Factura' ? 'selected' : '' }}>Factura</option>
                            <option value="Nota de Venta" {{ old('tipo_comprobante') === 'Nota de Venta' ? 'selected' : '' }}>Nota de Venta</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Número de comprobante *</label>
                        <input type="text" name="numero_comprobante" value="{{ old('numero_comprobante') }}" class="form-control" required placeholder="B001-000123">
                    </div>
                    <div class="form-group">
                        <label>Método de pago *</label>
                        <select name="metodo_pago" class="form-control" required>
                            <option value="Efectivo">Efectivo</option>
                            <option value="Transferencia">Transferencia</option>
                            <option value="Yape/Plin">Yape/Plin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Monto pagado (S/) *</label>
                        <input type="number" step="0.01" name="monto_pagado" value="{{ old('monto_pagado', $pedido?->monto_total ?? '') }}" class="form-control" min="0" required>
                    </div>
                </div>

                <button type="submit" class="btn btn--primary" style="margin-top:16px;"><i class="fa-solid fa-cash-register"></i> Registrar venta</button>
                <a href="{{ route('admin.ventas.index') }}" class="btn btn--outline">Cancelar</a>
            </form>
        </div>
    </div>

    @if($pedido)
    <div class="admin-card">
        <div class="admin-card__header"><span class="admin-card__title">Pedido #{{ str_pad($pedido->id, 6, '0', STR_PAD_LEFT) }}</span></div>
        <div class="admin-card__body" style="font-size:14px; line-height:1.6;">
            <div><strong>Cliente:</strong> {{ $pedido->cliente->nombre_razon_social ?? '—' }}</div>
            <div><strong>Total:</strong> S/ {{ number_format($pedido->monto_total, 2) }}</div>
            <div><strong>Comprobante solicitado:</strong> {{ $pedido->tipo_comprobante_solicitado }}</div>
            <div><strong>Dirección:</strong> {{ $pedido->direccion_envio }}</div>
            <hr style="margin:12px 0; border:none; border-top:1px solid #e2e8f0;">
            @foreach($pedido->detalles as $d)
                <div style="display:flex; justify-content:space-between;"><span>{{ $d->producto->nombre ?? '—' }} x{{ $d->cantidad }}</span><strong>S/ {{ number_format($d->subtotal,2) }}</strong></div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
