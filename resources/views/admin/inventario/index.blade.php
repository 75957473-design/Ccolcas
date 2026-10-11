@extends('layouts.admin')
@section('title', 'Inventario')
@section('page_title', 'Gestión de Inventario')

@section('content')
<form method="GET" action="{{ route('admin.inventario.index') }}" style="display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap;">
    <input type="text" name="q" value="{{ $q }}" placeholder="Buscar producto" class="form-control" style="min-width:260px;">
    <select name="almacen_id" class="form-control">
        <option value="">Todos los almacenes</option>
        @foreach($almacenes as $alm)
            <option value="{{ $alm->id }}" {{ (string) $almacenId === (string) $alm->id ? 'selected' : '' }}>{{ $alm->nombre }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn--outline btn--sm"><i class="fa-solid fa-magnifying-glass"></i> Filtrar</button>
</form>

<div style="display:grid; grid-template-columns: 1fr 380px; gap:20px;">
    <div>
        <div class="admin-card">
            <div class="admin-card__header"><span class="admin-card__title">Stock por almacén</span></div>
            <div class="admin-card__body" style="padding:0;">
                <table class="admin-table">
                    <thead><tr><th>Producto</th><th>Almacén</th><th>Cantidad</th><th>Estado</th></tr></thead>
                    <tbody>
                    @forelse($productos as $prod)
                        @php $total = $prod->stockTotal(); @endphp
                        <tr class="{{ $prod->enStockCritico() ? 'row--danger' : '' }}">
                            <td>
                                <div style="font-weight:700;">{{ $prod->nombre }}</div>
                                <div style="font-size:12px; color:#64748b;">{{ $prod->codigo ?? '' }} • Mín: {{ $prod->stock_minimo }}</div>
                            </td>
                            <td>
                                @foreach($prod->stocks as $s)
                                    <div>{{ $s->almacen->nombre ?? '—' }}: <strong>{{ $s->cantidad }}</strong></div>
                                @endforeach
                                @if($prod->stocks->isEmpty()) <span style="color:#94a3b8;">Sin stock registrado</span> @endif
                            </td>
                            <td><strong>{{ $total }}</strong></td>
                            <td>
                                @if($total == 0) <span class="badge badge--danger">Agotado</span>
                                @elseif($prod->enStockCritico()) <span class="badge badge--danger">Crítico</span>
                                @else <span class="badge badge--success">OK</span> @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty-state">Sin productos.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div style="margin-top:12px;">{{ $productos->links() }}</div>

        <div class="admin-card" style="margin-top:20px;">
            <div class="admin-card__header"><span class="admin-card__title">Historial de movimientos (últimos 20)</span></div>
            <div class="admin-card__body" style="padding:0;">
                <table class="admin-table">
                    <thead><tr><th>Fecha</th><th>Producto</th><th>Tipo</th><th>Cant.</th><th>Usuario</th></tr></thead>
                    <tbody>
                    @forelse($movimientos as $m)
                        <tr>
                            <td style="font-size:12px;">{{ \Carbon\Carbon::parse($m->fecha_movimiento)->format('d/m/Y H:i') }}</td>
                            <td>{{ $m->producto->nombre ?? '—' }}</td>
                            <td><span class="badge {{ $m->tipoMovimiento->tipo === 'ENTRADA' ? 'badge--success' : 'badge--danger' }}">{{ $m->tipoMovimiento->nombre ?? '' }}</span></td>
                            <td>{{ $m->cantidad }}</td>
                            <td>{{ $m->usuario->nombre_completo ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state">Sin movimientos.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div style="display:flex; flex-direction:column; gap:20px;">
        <div class="admin-card">
            <div class="admin-card__header"><span class="admin-card__title"><i class="fa-solid fa-right-left"></i> Registrar movimiento</span></div>
            <div class="admin-card__body">
                <form action="{{ route('admin.inventario.movimiento') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Producto *</label>
                        <select name="producto_id" class="form-control" required>
                            <option value="">Seleccionar</option>
                            @foreach(\App\Models\Producto::orderBy('nombre')->get() as $p)
                                <option value="{{ $p->id }}">{{ $p->nombre }} ({{ $p->codigo ?? 's/c' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Almacén *</label>
                        <select name="almacen_id" class="form-control" required>
                            @foreach($almacenes as $a)
                                <option value="{{ $a->id }}">{{ $a->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tipo de movimiento *</label>
                        <select name="tipo_movimiento_id" class="form-control" required>
                            @foreach($tiposMovimiento as $t)
                                <option value="{{ $t->id }}">{{ $t->nombre }} ({{ $t->tipo }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Cantidad *</label>
                        <input type="number" name="cantidad" class="form-control" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Motivo</label>
                        <input type="text" name="motivo" class="form-control" placeholder="Ej. Compra, ajuste, merma...">
                    </div>
                    <button type="submit" class="btn btn--primary" style="width:100%; justify-content:center;">Registrar movimiento</button>
                </form>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card__header"><span class="admin-card__title"><i class="fa-solid fa-sliders"></i> Ajuste directo de stock</span></div>
            <div class="admin-card__body">
                <form action="{{ route('admin.inventario.ajustar') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Producto *</label>
                        <select name="producto_id" class="form-control" required>
                            <option value="">Seleccionar</option>
                            @foreach(\App\Models\Producto::orderBy('nombre')->get() as $p)
                                <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Almacén *</label>
                        <select name="almacen_id" class="form-control" required>
                            @foreach($almacenes as $a)
                                <option value="{{ $a->id }}">{{ $a->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nueva cantidad *</label>
                        <input type="number" name="cantidad" class="form-control" min="0" required>
                        <span class="form-help">Reemplaza el stock actual.</span>
                    </div>
                    <div class="form-group">
                        <label>Motivo</label>
                        <input type="text" name="motivo" class="form-control" placeholder="Ej. Conteo físico">
                    </div>
                    <button type="submit" class="btn btn--dark" style="width:100%; justify-content:center;">Ajustar stock</button>
                </form>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card__header"><span class="admin-card__title"><i class="fa-solid fa-arrow-right-arrow-left"></i> Transferencia entre almacenes</span></div>
            <div class="admin-card__body">
                <form action="{{ route('admin.inventario.transferir') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Producto *</label>
                        <select name="producto_id" class="form-control" required>
                            <option value="">Seleccionar</option>
                            @foreach(\App\Models\Producto::orderBy('nombre')->get() as $p)
                                <option value="{{ $p->id }}">{{ $p->nombre }} ({{ $p->codigo ?? 's/c' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Almacén origen *</label>
                        <select name="almacen_origen_id" class="form-control" required>
                            @foreach($almacenes as $a)
                                <option value="{{ $a->id }}">{{ $a->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Almacén destino *</label>
                        <select name="almacen_destino_id" class="form-control" required>
                            @foreach($almacenes as $a)
                                <option value="{{ $a->id }}">{{ $a->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Cantidad *</label>
                        <input type="number" name="cantidad" class="form-control" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Motivo</label>
                        <input type="text" name="motivo" class="form-control" placeholder="Ej. Reabastecimiento tienda">
                    </div>
                    <button type="submit" class="btn btn--dark" style="width:100%; justify-content:center;"><i class="fa-solid fa-right-left"></i> Transferir</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
