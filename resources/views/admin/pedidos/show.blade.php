@extends('layouts.admin')
@section('title', 'Pedido #' . str_pad($pedido->id, 6, '0', STR_PAD_LEFT))
@section('page_title', 'Pedido #' . str_pad($pedido->id, 6, '0', STR_PAD_LEFT))

@section('content')
@php
    $flujo = ['Pendiente','Confirmado','En Almacén','Enviado','Entregado'];
    $esCancelado = ($pedido->estado->nombre ?? '') === 'Cancelado';
    $actualIndex = array_search($pedido->estado->nombre ?? 'Pendiente', $flujo);
@endphp

{{-- Stepper lineal --}}
@if(!$esCancelado)
    <div class="stepper" style="margin-bottom:16px;">
        @foreach($flujo as $idx => $nombre)
            @php $cls = $idx < $actualIndex ? 'stepper__step--done' : ($idx === $actualIndex ? 'stepper__step--current' : ''); @endphp
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
        <div class="admin-card">
            <div class="admin-card__header">
                <span class="admin-card__title">Detalle del pedido</span>
                <span class="badge badge--pending">{{ $pedido->estado->nombre ?? 'Pendiente' }}</span>
            </div>
            <div class="admin-card__body" style="padding:0;">
                <table class="admin-table">
                    <thead><tr><th>Producto</th><th>Precio unit.</th><th>Cant.</th><th>Subtotal</th></tr></thead>
                    <tbody>
                    @foreach($pedido->detalles as $det)
                        <tr>
                            <td><strong>{{ $det->producto->nombre ?? '—' }}</strong><div style="font-size:12px; color:#64748b;">{{ $det->producto->codigo ?? '' }}</div></td>
                            <td>S/ {{ number_format($det->precio_unitario, 2) }}</td>
                            <td>{{ $det->cantidad }}</td>
                            <td style="font-weight:700;">S/ {{ number_format($det->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div style="display:flex; justify-content:space-between; padding:16px 20px; background:#f8fafc; border-top:1px solid #e2e8f0; font-weight:800; font-size:16px;">
                    <span>Total</span><span>S/ {{ number_format($pedido->monto_total, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="admin-card" style="margin-top:16px;">
            <div class="admin-card__header"><span class="admin-card__title">Información de entrega</span></div>
            <div class="admin-card__body" style="font-size:14px; line-height:1.6;">
                <div><strong>Cliente:</strong> {{ $pedido->cliente->nombre_razon_social ?? '—' }} ({{ $pedido->cliente->tipo_documento ?? '' }} {{ $pedido->cliente->numero_documento ?? '' }})</div>
                <div><strong>Teléfono:</strong> {{ $pedido->cliente->telefono ?? '—' }}</div>
                <div><strong>Dirección de envío:</strong> {{ $pedido->direccion_envio }}</div>
                <div><strong>Comprobante solicitado:</strong> {{ $pedido->tipo_comprobante_solicitado }}</div>
                @if($pedido->observaciones)<div><strong>Observaciones cliente:</strong> {{ $pedido->observaciones }}</div>@endif
                <div><strong>Fecha (confirmado por cliente vía web):</strong> {{ \Carbon\Carbon::parse($pedido->fecha_pedido)->format('d/m/Y H:i') }}</div>
                @if($pedido->vendedor)<div><strong>Atendido por:</strong> {{ $pedido->vendedor->nombre_completo }} ({{ $pedido->vendedor->rol->nombre ?? '' }})</div>@endif
                @if($pedido->venta)
                    <div style="margin-top:10px; padding:10px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px;">
                        <strong>Venta asociada:</strong> {{ $pedido->venta->tipo_comprobante }} {{ $pedido->venta->numero_comprobante }} — S/ {{ number_format($pedido->venta->monto_pagado,2) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div style="display:flex; flex-direction:column; gap:16px;">
        {{-- Acción por rol: solo transiciones permitidas --}}
        <div class="admin-card">
            <div class="admin-card__header"><span class="admin-card__title">Actualizar estado</span></div>
            <div class="admin-card__body">
                <div style="font-size:12px; color:#64748b; margin-bottom:10px;">
                    Tu rol: <strong>{{ $rol }}</strong> · Estado actual: <strong>{{ $pedido->estado->nombre ?? '—' }}</strong>
                </div>

                @if($estadosPermitidos->isEmpty())
                    <div style="padding:10px; background:#fef3c7; border:1px solid #fcd34d; border-radius:6px; font-size:13px; color:#92400e;">
                        @if(in_array($pedido->estado->nombre, ['Entregado','Cancelado']))
                            Pedido en estado terminal. No hay más transiciones.
                        @else
                            No tienes permisos para avanzar este pedido desde <strong>{{ $pedido->estado->nombre }}</strong>.
                            <br>Transición requiere: 
                            @php $sig = \App\Services\PedidoWorkflowService::transicionesDesde($pedido->estado->nombre ?? ''); @endphp
                            @foreach($sig as $dest)
                                <span class="badge" style="background:#f1f5f9;">{{ $dest }} ({{ implode(', ', \App\Services\PedidoWorkflowService::ROLE_FOR_TARGET[$dest] ?? []) }})</span>
                            @endforeach
                        @endif
                    </div>
                @else
                    <form action="{{ route('admin.pedidos.estado', $pedido) }}" method="POST">
                        @csrf @method('PATCH')
                        <div class="form-group">
                            <label>Siguiente estado</label>
                            <select name="estado_id" class="form-control" required>
                                <option value="">— Selecciona —</option>
                                @foreach($estadosPermitidos as $e)
                                    <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="form-help">Solo ves las transiciones que tu rol puede ejecutar.</p>
                        </div>
                        <div class="form-group">
                            <label>Observación (opcional)</label>
                            <textarea name="observacion" rows="2" class="form-control" maxlength="500" placeholder="Ej: Verificado stock, cliente contactado..."></textarea>
                        </div>
                        <button type="submit" class="btn btn--primary" style="width:100%; justify-content:center;"><i class="fa-solid fa-floppy-disk"></i> Confirmar cambio</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Timeline auditable --}}
        <div class="admin-card">
            <div class="admin-card__header"><span class="admin-card__title"><i class="fa-solid fa-clock-rotate-left"></i> Seguimiento</span></div>
            <div class="admin-card__body">
                <div class="admin-timeline">
                    @forelse($pedido->historial->sortBy('created_at') as $h)
                        <div class="admin-timeline__item admin-timeline__item--done">
                            <div style="font-weight:700; font-size:13px; color:#0f172a;">{{ $h->estadoNuevo->nombre ?? '—' }}</div>
                            <div style="font-size:12px; color:#64748b;">{{ $h->created_at->format('d/m/Y H:i') }} • {{ $h->usuario->nombre_completo ?? 'Sistema' }} ({{ $h->usuario->rol->nombre ?? '' }})</div>
                            @if($h->observacion)<div style="font-size:12px; color:#334155; margin-top:4px;">{{ $h->observacion }}</div>@endif
                            @if($h->estadoAnterior)<div style="font-size:11px; color:#94a3b8;">← {{ $h->estadoAnterior->nombre }}</div>@endif
                        </div>
                    @empty
                        <div style="font-size:13px; color:#64748b;">Sin historial. El pedido fue creado como Pendiente por el cliente vía web.</div>
                    @endforelse
                </div>
            </div>
        </div>

        @if(!$pedido->venta && in_array($pedido->estado->nombre, ['Enviado','Entregado','En Almacén']))
        <div class="admin-card">
            <div class="admin-card__body">
                <a href="{{ route('admin.ventas.create', ['pedido_id' => $pedido->id]) }}" class="btn btn--dark" style="width:100%; justify-content:center;"><i class="fa-solid fa-cash-register"></i> Registrar venta</a>
                <p class="form-help" style="margin-top:8px; text-align:center;">Convierte el pedido en venta (descuenta stock + historial).</p>
            </div>
        </div>
        @endif

        <a href="{{ route('admin.pedidos.index') }}" class="btn btn--outline" style="justify-content:center;"><i class="fa-solid fa-arrow-left"></i> Volver a pedidos</a>
    </div>
</div>
@endsection
