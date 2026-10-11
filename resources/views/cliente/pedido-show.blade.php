<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido #{{ str_pad($pedido->id, 6, '0', STR_PAD_LEFT) }} | CCOLCAS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/inicio.css', 'resources/css/pedidos.css'])
</head>
<body>
<header class="top-header">
    <div class="header-container">
        <a href="{{ url('/') }}" class="logo"><i class="fa-solid fa-pump-soap"></i> CCOLCAS</a>
        <a href="{{ route('cliente.mis-pedidos') }}" style="color:#fff; text-decoration:none;"><i class="fa-solid fa-arrow-left"></i> Mis pedidos</a>
    </div>
</header>

<div class="orders-container">
    <div class="orders-header">
        <h1 class="orders-title"><i class="fa-solid fa-receipt"></i> Pedido #{{ str_pad($pedido->id, 6, '0', STR_PAD_LEFT) }}</h1>
        <span class="badge-status status-{{ Str::slug($pedido->estado->nombre ?? 'pendiente') }}">{{ $pedido->estado->nombre ?? 'Pendiente' }}</span>
    </div>

    @php
        $flujo = ['Pendiente','Confirmado','En Almacén','Enviado','Entregado'];
        $actualIndex = array_search($pedido->estado->nombre ?? 'Pendiente', $flujo);
        $esCancelado = ($pedido->estado->nombre ?? '') === 'Cancelado';
    @endphp

    @if(!$esCancelado)
        <div class="stepper" style="margin-bottom:18px;">
            @foreach($flujo as $idx => $nombre)
                @php
                    $cls = $idx < $actualIndex ? 'stepper__step--done' : ($idx === $actualIndex ? 'stepper__step--current' : '');
                @endphp
                <div class="stepper__step {{ $cls }}">{{ $nombre }}</div>
            @endforeach
        </div>
    @else
        <div class="alert alert--error" style="background:#fef2f2; border:1px solid #fecaca; padding:12px; border-radius:8px; margin-bottom:16px; text-align:center; font-weight:700; color:#991b1b;">
            Pedido cancelado
        </div>
    @endif

    <div style="display:grid; grid-template-columns: 1fr 360px; gap:20px;">
        <div>
            <div class="order-card">
                <div class="order-card-header">
                    <span class="order-code">Detalle</span>
                    <span class="order-date">{{ \Carbon\Carbon::parse($pedido->fecha_pedido)->format('d/m/Y H:i') }}</span>
                </div>
                <div class="order-card-body" style="padding:0;">
                    <table class="order-details-table">
                        <thead><tr><th>Producto</th><th>Precio unit.</th><th>Cant.</th><th>Subtotal</th></tr></thead>
                        <tbody>
                        @foreach($pedido->detalles as $det)
                            <tr>
                                <td><strong>{{ $det->producto->nombre ?? '—' }}</strong></td>
                                <td>S/ {{ number_format($det->precio_unitario, 2) }}</td>
                                <td>{{ $det->cantidad }}</td>
                                <td><strong>S/ {{ number_format($det->subtotal, 2) }}</strong></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div style="display:flex; justify-content:space-between; padding:14px 20px; background:#f8fafc; border-top:1px solid #e2e8f0; font-weight:800;">
                        <span>Total</span><span>S/ {{ number_format($pedido->monto_total, 2) }}</span>
                    </div>
                </div>
                <div class="order-card-footer">
                    <div class="order-info-meta">
                        <div><strong>Comprobante:</strong> {{ $pedido->tipo_comprobante_solicitado }}</div>
                        <div><strong>Dirección:</strong> {{ $pedido->direccion_envio }}</div>
                        @if($pedido->observaciones)<div><strong>Nota:</strong> {{ $pedido->observaciones }}</div>@endif
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="order-card" style="padding:16px;">
                <h3 style="font-size:14px; margin-bottom:10px;"><i class="fa-solid fa-clock-rotate-left"></i> Seguimiento</h3>
                <div class="timeline">
                    @forelse($pedido->historial->sortBy('created_at') as $h)
                        <div class="timeline__item timeline__item--done">
                            <div class="timeline__label">{{ $h->estadoNuevo->nombre ?? '—' }}</div>
                            <div class="timeline__meta">{{ $h->created_at->format('d/m/Y H:i') }} • {{ $h->usuario->nombre_completo ?? 'Sistema' }} ({{ $h->usuario->rol->nombre ?? '' }})</div>
                            @if($h->observacion)<div class="timeline__obs">{{ $h->observacion }}</div>@endif
                            @if($h->estadoAnterior)<div class="timeline__meta">← {{ $h->estadoAnterior->nombre }}</div>@endif
                        </div>
                    @empty
                        <div style="font-size:13px; color:#64748b;">Sin historial aún.</div>
                    @endforelse
                </div>
                @if($pedido->venta)
                    <div style="margin-top:12px; padding:10px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; font-size:13px;">
                        <strong>Venta:</strong> {{ $pedido->venta->tipo_comprobante }} {{ $pedido->venta->numero_comprobante }} — S/ {{ number_format($pedido->venta->monto_pagado,2) }}
                    </div>
                @endif
            </div>

            <a href="{{ route('cliente.mis-pedidos') }}" class="btn-back-store" style="margin-top:12px; display:inline-flex; justify-content:center; width:100%;"><i class="fa-solid fa-arrow-left"></i> Volver a mis pedidos</a>
        </div>
    </div>
</div>
</body>
</html>
