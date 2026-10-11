<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\StockPorAlmacen;
use App\Models\TipoMovimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $almacenId = $request->input('almacen_id');

        $productos = Producto::with(['categoria', 'stocks.almacen'])
            ->when($q !== '', fn ($qb) => $qb->where(fn ($w) => $w->where('nombre', 'like', "%{$q}%")->orWhere('codigo', 'like', "%{$q}%")))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        $almacenes = Almacen::orderBy('nombre')->get();

        $stocks = StockPorAlmacen::with(['producto', 'almacen'])
            ->when($almacenId, fn ($qb) => $qb->where('almacen_id', $almacenId))
            ->when($q !== '', fn ($qb) => $qb->whereHas('producto', fn ($w) => $w->where('nombre', 'like', "%{$q}%")))
            ->get();

        $movimientos = MovimientoInventario::with(['producto', 'almacen', 'tipoMovimiento', 'usuario'])
            ->latest('fecha_movimiento')
            ->take(20)
            ->get();

        $tiposMovimiento = TipoMovimiento::orderBy('nombre')->get();

        return view('admin.inventario.index', compact('productos', 'almacenes', 'stocks', 'movimientos', 'tiposMovimiento', 'q', 'almacenId'));
    }

    /**
     * PA3 - Proceso de Control: Transferencia multialmacén (Incremento 3).
     * Descuenta del origen y suma al destino, con dos movimientos y transacción atómica.
     */
    public function transferir(Request $request)
    {
        $data = $request->validate([
            'producto_id' => ['required', 'exists:productos,id'],
            'almacen_origen_id' => ['required', 'exists:almacenes,id', 'different:almacen_destino_id'],
            'almacen_destino_id' => ['required', 'exists:almacenes,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'motivo' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($data) {
            $origen = StockPorAlmacen::where('producto_id', $data['producto_id'])
                ->where('almacen_id', $data['almacen_origen_id'])
                ->lockForUpdate()
                ->first();

            $stockOrigen = $origen?->cantidad ?? 0;
            if ($data['cantidad'] > $stockOrigen) {
                throw new \InvalidArgumentException("Stock insuficiente en origen. Disponible: {$stockOrigen} unidades.");
            }

            // Descontar origen
            $origen->decrement('cantidad', $data['cantidad']);

            // Sumar destino
            $destino = StockPorAlmacen::firstOrCreate(
                ['producto_id' => $data['producto_id'], 'almacen_id' => $data['almacen_destino_id']],
                ['cantidad' => 0]
            );
            $destino->increment('cantidad', $data['cantidad']);

            // Tipos para la transferencia
            $tipoSalida = TipoMovimiento::firstOrCreate(['nombre' => 'Salida por Transferencia'], ['tipo' => 'SALIDA']);
            $tipoEntrada = TipoMovimiento::firstOrCreate(['nombre' => 'Entrada por Transferencia'], ['tipo' => 'ENTRADA']);

            MovimientoInventario::create([
                'producto_id' => $data['producto_id'],
                'almacen_id' => $data['almacen_origen_id'],
                'tipo_movimiento_id' => $tipoSalida->id,
                'cantidad' => $data['cantidad'],
                'motivo' => $data['motivo'] ?? "Transferencia a almacén #{$data['almacen_destino_id']}",
                'usuario_id' => Auth::id(),
                'fecha_movimiento' => now(),
            ]);

            MovimientoInventario::create([
                'producto_id' => $data['producto_id'],
                'almacen_id' => $data['almacen_destino_id'],
                'tipo_movimiento_id' => $tipoEntrada->id,
                'cantidad' => $data['cantidad'],
                'motivo' => $data['motivo'] ?? "Transferencia desde almacén #{$data['almacen_origen_id']}",
                'usuario_id' => Auth::id(),
                'fecha_movimiento' => now(),
            ]);
        });

        return back()->with('success', 'Transferencia registrada correctamente.');
    }

    public function movimiento(Request $request)
    {
        $data = $request->validate([
            'producto_id' => ['required', 'exists:productos,id'],
            'almacen_id' => ['required', 'exists:almacenes,id'],
            'tipo_movimiento_id' => ['required', 'exists:tipos_movimiento,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'motivo' => ['nullable', 'string', 'max:500'],
        ]);

        $tipo = TipoMovimiento::findOrFail($data['tipo_movimiento_id']);

        DB::transaction(function () use ($data, $tipo) {
            $stock = StockPorAlmacen::where('producto_id', $data['producto_id'])
                ->where('almacen_id', $data['almacen_id'])
                ->lockForUpdate()
                ->first();

            $actual = $stock?->cantidad ?? 0;

            if ($tipo->tipo === 'SALIDA' && $data['cantidad'] > $actual) {
                throw new \InvalidArgumentException("Stock insuficiente. Disponible: {$actual} unidades.");
            }

            $nuevo = $tipo->tipo === 'ENTRADA' ? $actual + $data['cantidad'] : $actual - $data['cantidad'];

            StockPorAlmacen::updateOrCreate(
                ['producto_id' => $data['producto_id'], 'almacen_id' => $data['almacen_id']],
                ['cantidad' => $nuevo]
            );

            MovimientoInventario::create([
                'producto_id' => $data['producto_id'],
                'almacen_id' => $data['almacen_id'],
                'tipo_movimiento_id' => $data['tipo_movimiento_id'],
                'cantidad' => $data['cantidad'],
                'motivo' => $data['motivo'] ?? $tipo->nombre,
                'usuario_id' => Auth::id(),
                'fecha_movimiento' => now(),
            ]);
        });

        return back()->with('success', 'Movimiento registrado correctamente. Stock actualizado.');
    }

    public function ajustar(Request $request)
    {
        $data = $request->validate([
            'producto_id' => ['required', 'exists:productos,id'],
            'almacen_id' => ['required', 'exists:almacenes,id'],
            'cantidad' => ['required', 'integer', 'min:0'],
            'motivo' => ['nullable', 'string', 'max:500'],
        ]);

        $tipoAjuste = TipoMovimiento::firstOrCreate(
            ['nombre' => 'Ajuste de inventario'],
            ['tipo' => 'ENTRADA']
        );

        DB::transaction(function () use ($data, $tipoAjuste) {
            StockPorAlmacen::updateOrCreate(
                ['producto_id' => $data['producto_id'], 'almacen_id' => $data['almacen_id']],
                ['cantidad' => $data['cantidad']]
            );

            MovimientoInventario::create([
                'producto_id' => $data['producto_id'],
                'almacen_id' => $data['almacen_id'],
                'tipo_movimiento_id' => $tipoAjuste->id,
                'cantidad' => $data['cantidad'],
                'motivo' => $data['motivo'] ?? 'Ajuste manual de stock',
                'usuario_id' => Auth::id(),
                'fecha_movimiento' => now(),
            ]);
        });

        return back()->with('success', 'Stock ajustado correctamente.');
    }
}
