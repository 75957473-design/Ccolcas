@extends('layouts.admin')
@section('title', 'Cliente ' . $cliente->nombre_razon_social)
@section('page_title', $cliente->nombre_razon_social)

@section('content')
<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
    <div class="admin-card">
        <div class="admin-card__header"><span class="admin-card__title">Datos del cliente</span><a href="{{ route('admin.clientes.edit', $cliente) }}" class="btn btn--ghost btn--sm"><i class="fa-solid fa-pen"></i> Editar</a></div>
        <div class="admin-card__body" style="line-height:1.8; font-size:14px;">
            <div><strong>Razón social:</strong> {{ $cliente->nombre_razon_social }}</div>
            <div><strong>Documento:</strong> {{ $cliente->tipo_documento }} {{ $cliente->numero_documento }}</div>
            <div><strong>Teléfono:</strong> {{ $cliente->telefono ?? '—' }}</div>
            <div><strong>Email:</strong> {{ $cliente->email ?? $cliente->usuario->email ?? '—' }}</div>
            <div><strong>Dirección de entrega:</strong> {{ $cliente->direccion_entrega ?? '—' }}</div>
            <div><strong>Persona que recibe:</strong> {{ $cliente->persona_recepcion ?? '—' }}</div>
            <div><strong>Usuario vinculado:</strong> {{ $cliente->usuario->nombre_completo ?? 'Sin usuario' }}</div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card__header"><span class="admin-card__title">Pedidos ({{ $cliente->pedidos->count() }})</span></div>
        <div class="admin-card__body" style="padding:0;">
            @if($cliente->pedidos->isEmpty())
                <div class="empty-state">Este cliente aún no tiene pedidos.</div>
            @else
                <table class="admin-table">
                    <thead><tr><th>#</th><th>Fecha</th><th>Estado</th><th>Total</th></tr></thead>
                    <tbody>
                    @foreach($cliente->pedidos as $ped)
                        <tr>
                            <td><a href="{{ route('admin.pedidos.show', $ped) }}">#{{ str_pad($ped->id, 6, '0', STR_PAD_LEFT) }}</a></td>
                            <td style="font-size:13px;">{{ \Carbon\Carbon::parse($ped->fecha_pedido)->format('d/m/Y') }}</td>
                            <td><span class="badge badge--pending">{{ $ped->estado->nombre ?? '' }}</span></td>
                            <td>S/ {{ number_format($ped->monto_total,2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

<a href="{{ route('admin.clientes.index') }}" class="btn btn--outline" style="margin-top:16px;"><i class="fa-solid fa-arrow-left"></i> Volver</a>
@endsection
