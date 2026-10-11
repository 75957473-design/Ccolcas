<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MovimientoInventario;
use App\Models\Pedido;
use App\Models\StockPorAlmacen;
use App\Models\TipoMovimiento;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $ventas = Venta::with(['pedido.cliente', 'pedido.detalles.producto', 'usuario'])
            ->when($q !== '', fn ($qb) => $qb->where('numero_comprobante', 'like', "%{$q}%")->orWhere('tipo_comprobante', 'like', "%{$q}%"))
            ->latest('fecha_emision')
            ->paginate(12)
            ->withQueryString();

        return view('admin.ventas.index', compact('ventas', 'q'));
    }

    public function create(Request $request)
    {
        $pedidoId = $request->input('pedido_id');
        $pedido = $pedidoId ? Pedido::with(['cliente', 'estado', 'detalles.producto', 'venta'])->find($pedidoId) : null;

        $pedidosPendientes = Pedido::with(['cliente', 'estado'])
            ->whereDoesntHave('venta')
            ->whereHas('estado', fn ($q) => $q->where('nombre', '!=', 'Cancelado'))
            ->orderByDesc('fecha_pedido')
            ->get();

        return view('admin.ventas.create', compact('pedido', 'pedidosPendientes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pedido_id' => ['required', 'exists:pedidos,id', 'unique:ventas,pedido_id'],
            'tipo_comprobante' => ['required', 'in:Boleta,Factura,Nota de Venta'],
            'numero_comprobante' => ['required', 'string', 'max:20'],
            'metodo_pago' => ['required', 'in:Efectivo,Transferencia,Yape/Plin'],
            'monto_pagado' => ['required', 'numeric', 'min:0'],
        ]);

        $pedido = Pedido::with('detalles')->findOrFail($data['pedido_id']);

        if ($pedido->venta) {
            return back()->with('error', 'Este pedido ya tiene una venta registrada.')->withInput();
        }

        DB::transaction(function () use ($data, $pedido) {
            $venta = Venta::create([
                'pedido_id' => $data['pedido_id'],
                'usuario_id' => Auth::id(),
                'tipo_comprobante' => $data['tipo_comprobante'],
                'numero_comprobante' => $data['numero_comprobante'],
                'metodo_pago' => $data['metodo_pago'],
                'monto_pagado' => $data['monto_pagado'],
                'fecha_emision' => now(),
            ]);

            // Descontar stock y registrar movimientos de salida
            $tipoSalida = TipoMovimiento::firstOrCreate(
                ['nombre' => 'Salida por Venta'],
                ['tipo' => 'SALIDA']
            );

            foreach ($pedido->detalles as $detalle) {
                $stock = StockPorAlmacen::where('producto_id', $detalle->producto_id)
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
                    $stock->decrement('cantidad', $detalle->cantidad);
                }

                MovimientoInventario::create([
                    'producto_id' => $detalle->producto_id,
                    'almacen_id' => $detalle->almacen_id ?? $stock?->almacen_id ?? 1,
                    'tipo_movimiento_id' => $tipoSalida->id,
                    'cantidad' => $detalle->cantidad,
                    'motivo' => "Venta #{$venta->numero_comprobante} - Pedido #{$pedido->id}",
                    'usuario_id' => Auth::id(),
                    'fecha_movimiento' => now(),
                ]);
            }

            // Historial y avance de estado vía workflow (respeta permisos del rol que registra la venta)
            $rol = Auth::user()->rol->nombre ?? '';
            $estadoActual = $pedido->estado->nombre ?? null;
            // Venta solo tiene sentido desde Enviado/Entregado; si está antes, lo llevamos a Entregado si el rol lo permite
            if ($estadoActual !== 'Entregado' && \App\Services\PedidoWorkflowService::puedeTransitar($pedido->fresh('estado'), 'Entregado', $rol)) {
                $anteriorId = $pedido->estado_id;
                \App\Services\PedidoWorkflowService::transitar($pedido, 'Entregado', Auth::id());
                \App\Models\HistorialPedido::create([
                    'pedido_id' => $pedido->id,
                    'estado_anterior_id' => $anteriorId,
                    'estado_nuevo_id' => $pedido->fresh('estado')->estado_id,
                    'usuario_id' => Auth::id(),
                    'observacion' => 'Venta ' . $venta->tipo_comprobante . ' ' . $venta->numero_comprobante . ' registrada.',
                ]);
            }
        });

        return redirect()->route('admin.ventas.index')->with('success', 'Venta registrada y stock actualizado correctamente.');
    }

    public function show(Venta $venta)
    {
        $venta->load(['pedido.cliente', 'pedido.detalles.producto', 'usuario']);

        return view('admin.ventas.show', compact('venta'));
    }
}
