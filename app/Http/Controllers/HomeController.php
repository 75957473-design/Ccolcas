<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Carga los productos activos junto con categoría, unidad de medida y stock por almacén
        $productos = Producto::with(['categoria', 'unidadMedida', 'stocks'])
            ->where('activo', true)
            ->latest()
            ->take(20)
            ->get();

        $categorias = Categoria::where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('welcome', compact('productos', 'categorias'));
    }

    public function buscar(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $categoriaSeleccionada = trim((string) $request->input('categoria', ''));

        $consulta = Producto::with(['categoria', 'unidadMedida', 'stocks'])
            ->where('activo', true)
            ->when($q !== '', function ($consulta) use ($q) {
                $consulta->where(function ($coincidencias) use ($q) {
                    $coincidencias->where('nombre', 'LIKE', "%{$q}%")
                        ->orWhere('descripcion', 'LIKE', "%{$q}%")
                        ->orWhere('codigo', 'LIKE', "%{$q}%");
                });
            })
            ->when($categoriaSeleccionada !== '', function ($consulta) use ($categoriaSeleccionada) {
                $consulta->whereHas('categoria', function ($categoria) use ($categoriaSeleccionada) {
                    $categoria->where('slug', $categoriaSeleccionada)
                        ->where('activo', true);
                });
            });

        $productos = $consulta->latest()->get();

        $categorias = Categoria::where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('welcome', compact('productos', 'categorias', 'q', 'categoriaSeleccionada'));
    }

    

    public function carrito()
    {
        return view('carrito'); // Se implementará para la gestión de pedidos
    }

    public function misPedidos()
    {
        return view('cliente.pedidos');
    }

    public function dashboardAdmin()
    {
        $stats = [
            'productos' => \App\Models\Producto::count(),
            'productos_activos' => \App\Models\Producto::where('activo', true)->count(),
            'pedidos' => \App\Models\Pedido::count(),
            'pedidos_pendientes' => \App\Models\Pedido::whereHas('estado', fn ($q) => $q->where('nombre', 'Pendiente'))->count(),
            'ventas' => \App\Models\Venta::count(),
            'clientes' => \App\Models\Cliente::count(),
            'stock_critico' => \App\Models\Producto::with('stocks')->get()->filter(fn ($p) => $p->enStockCritico())->count(),
        ];

        $ultimosPedidos = \App\Models\Pedido::with(['cliente', 'estado'])->latest('fecha_pedido')->take(5)->get();
        $ultimasVentas = \App\Models\Venta::with(['pedido.cliente'])->latest('fecha_emision')->take(5)->get();

        return view('admin.dashboard', compact('stats', 'ultimosPedidos', 'ultimasVentas'));
    }
}