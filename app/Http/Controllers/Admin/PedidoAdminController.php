<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EstadoPedido;
use App\Models\HistorialPedido;
use App\Models\Pedido;
use App\Services\PedidoWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PedidoAdminController extends Controller
{
    public function index(Request $request)
    {
        $estadoId = $request->input('estado_id');
        $q = trim((string) $request->input('q', ''));

        $pedidos = Pedido::with(['cliente.usuario', 'estado', 'detalles.producto'])
            ->when($estadoId, fn ($qb) => $qb->where('estado_id', $estadoId))
            ->when($q !== '', function ($qb) use ($q) {
                $qb->where(function ($w) use ($q) {
                    $w->where('id', $q)
                      ->orWhere('direccion_envio', 'like', "%{$q}%")
                      ->orWhereHas('cliente', fn ($c) => $c->where('nombre_razon_social', 'like', "%{$q}%")->orWhere('numero_documento', 'like', "%{$q}%"));
                });
            })
            ->orderByDesc('fecha_pedido')
            ->paginate(12)
            ->withQueryString();

        $estados = EstadoPedido::orderBy('nombre')->get();

        return view('admin.pedidos.index', compact('pedidos', 'estados', 'estadoId', 'q'));
    }

    public function show(Pedido $pedido)
    {
        $pedido->load(['cliente.usuario', 'estado', 'detalles.producto', 'venta', 'vendedor', 'historial.estadoAnterior', 'historial.estadoNuevo', 'historial.usuario']);

        $rol = Auth::user()->rol->nombre ?? '';
        $permitidos = PedidoWorkflowService::estadosPermitidosParaRol($pedido, $rol);
        $estadosPermitidos = EstadoPedido::whereIn('nombre', $permitidos)->get();

        return view('admin.pedidos.show', compact('pedido', 'estadosPermitidos', 'rol'));
    }

    public function updateEstado(Request $request, Pedido $pedido)
    {
        $data = $request->validate([
            'estado_id' => ['required', 'exists:estados_pedido,id'],
            'observacion' => ['nullable', 'string', 'max:500'],
        ]);

        $usuario = Auth::user();
        $rol = $usuario->rol->nombre ?? '';
        $destino = EstadoPedido::findOrFail($data['estado_id']);

        if (! PedidoWorkflowService::puedeTransitar($pedido, $destino->nombre, $rol)) {
            return back()->with('error', 'Transición no permitida: ' . ($pedido->estado->nombre ?? '?') . ' → ' . $destino->nombre . ' no está permitida para tu rol (' . $rol . ').');
        }

        if ($pedido->estado_id === $destino->id) {
            return back()->with('error', 'El pedido ya está en ese estado.');
        }

        $anteriorId = $pedido->estado_id;

        PedidoWorkflowService::transitar($pedido, $destino->nombre, $usuario->id);

        HistorialPedido::create([
            'pedido_id' => $pedido->id,
            'estado_anterior_id' => $anteriorId,
            'estado_nuevo_id' => $destino->id,
            'usuario_id' => $usuario->id,
            'observacion' => $data['observacion'] ?? null,
        ]);

        return back()->with('success', 'Pedido actualizado a ' . $destino->nombre . ' correctamente.');
    }
}
