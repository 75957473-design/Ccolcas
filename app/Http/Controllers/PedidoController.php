<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Cliente;
use App\Models\EstadoPedido;
use App\Models\HistorialPedido;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    /**
     * Muestra el historial de pedidos del cliente.
     */
    public function index()
    {
        $usuario = Auth::user();

        $pedidos = Pedido::with(['estado', 'detalles.producto', 'historial.estadoNuevo', 'historial.usuario'])
            ->whereHas('cliente', function ($query) use ($usuario) {
                $query->where('usuario_id', $usuario->id);
            })
            ->orderBy('fecha_pedido', 'desc')
            ->get();

        return view('cliente.pedidos', compact('pedidos'));
    }

    /**
     * Vista detalle de un pedido propio del cliente (trazabilidad web).
     */
    public function show(Pedido $pedido)
    {
        $usuario = Auth::user();
        $esDueno = $pedido->cliente && $pedido->cliente->usuario_id === $usuario->id;

        if (! $esDueno && ! $usuario->esPersonal()) {
            abort(403);
        }

        $pedido->load(['estado', 'cliente.usuario', 'detalles.producto', 'vendedor', 'historial.estadoAnterior', 'historial.estadoNuevo', 'historial.usuario', 'venta']);

        return view('cliente.pedido-show', compact('pedido'));
    }

    /**
     * Vista de confirmación de compra (Checkout).
     */
    public function checkout()
    {
        $carrito = session()->get('carrito', []);

        if (empty($carrito)) {
            return redirect()->route('carrito.index')->with('error', 'El carrito está vacío.');
        }

        $usuario = Auth::user();
        $cliente = Cliente::where('usuario_id', $usuario->id)->first();

        return view('cliente.checkout', compact('carrito', 'cliente'));
    }

    /**
     * Procesa y registra el pedido en la Base de Datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'direccion_envio' => 'required|string|max:255',
            'tipo_comprobante_solicitado' => 'required|in:Boleta,Factura',
            'observaciones' => 'nullable|string',
        ]);

        $carrito = session()->get('carrito', []);

        if (empty($carrito)) {
            return redirect()->route('carrito.index')->with('error', 'No hay productos en el carrito.');
        }

        $usuario = Auth::user();

        // 1. Obtener o crear el registro de cliente asociado
        $cliente = Cliente::firstOrCreate(
            ['usuario_id' => $usuario->id],
            [
                'tipo_documento' => 'DNI',
                'numero_documento' => '00000000', // Actualizar luego en perfil
                'nombre_razon_social' => $usuario->nombre_completo,
                'email' => $usuario->email,
                'direccion_entrega' => $request->direccion_envio,
            ]
        );

        // 2. Obtener estado inicial (Pendiente)
        $estadoPendiente = EstadoPedido::firstOrCreate(['nombre' => 'Pendiente']);

        // 3. Transacción: recalcular precio/stock contra BD para evitar manipulación de sesión
        DB::transaction(function () use ($request, $cliente, $estadoPendiente, $carrito) {
            $ids = array_keys($carrito);
            $productos = \App\Models\Producto::with('stocks')->whereIn('id', $ids)->get()->keyBy('id');

            $montoTotal = 0;
            foreach ($carrito as $productoId => $item) {
                $prod = $productos->get($productoId);
                if (! $prod) {
                    throw new \InvalidArgumentException("Producto #{$productoId} no encontrado.");
                }
                if ($item['cantidad'] > $prod->stockTotal()) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        response()->redirectTo(route('carrito.index'))->withErrors(['cantidad' => "Stock insuficiente para {$prod->nombre}. Disponible: {$prod->stockTotal()}"])
                    );
                }
                // Usar precio real de BD, no el de sesión
                $montoTotal += (float) $prod->precio_venta * (int) $item['cantidad'];
            }

            $pedido = Pedido::create([
                'cliente_id' => $cliente->id,
                'estado_id' => $estadoPendiente->id,
                'tipo_comprobante_solicitado' => $request->tipo_comprobante_solicitado,
                'fecha_pedido' => now(),
                'monto_total' => round($montoTotal, 2),
                'direccion_envio' => $request->direccion_envio,
                'observaciones' => $request->observaciones,
            ]);

            foreach ($carrito as $productoId => $item) {
                $prod = $productos->get($productoId);
                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $productoId,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => (float) $prod->precio_venta,
                    'subtotal' => round((float) $prod->precio_venta * (int) $item['cantidad'], 2),
                ]);
            }

            // Historial inicial: creación en Pendiente
            HistorialPedido::create([
                'pedido_id' => $pedido->id,
                'estado_anterior_id' => null,
                'estado_nuevo_id' => $estadoPendiente->id,
                'usuario_id' => $cliente->usuario_id,
                'observacion' => 'Pedido confirmado por el cliente vía web.',
            ]);
        });

        // 4. Limpiar el carrito de la sesión
        session()->forget('carrito');

        return redirect()->route('cliente.mis-pedidos')->with('success', '¡Pedido realizado con éxito!');
    }
}