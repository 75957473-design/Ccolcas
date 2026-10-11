<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $categoriaId = $request->input('categoria_id');
        $estado = $request->input('estado');

        $productos = Producto::with(['categoria', 'unidadMedida', 'stocks'])
            ->when($q !== '', fn ($qb) => $qb->where(function ($w) use ($q) {
                $w->where('nombre', 'like', "%{$q}%")
                  ->orWhere('codigo', 'like', "%{$q}%")
                  ->orWhere('descripcion', 'like', "%{$q}%");
            }))
            ->when($categoriaId, fn ($qb) => $qb->where('categoria_id', $categoriaId))
            ->when($estado === 'activos', fn ($qb) => $qb->where('activo', true))
            ->when($estado === 'inactivos', fn ($qb) => $qb->where('activo', false))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categorias = Categoria::orderBy('nombre')->get();

        return view('admin.productos.index', compact('productos', 'categorias', 'q', 'categoriaId', 'estado'));
    }

    public function create()
    {
        $categorias = Categoria::orderBy('nombre')->get();
        $unidades = UnidadMedida::orderBy('nombre')->get();

        return view('admin.productos.create', compact('categorias', 'unidades'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $data['precio_venta'] = $this->calcularPrecioVenta($data);

        Producto::create($data);

        return redirect()->route('admin.productos.index')->with('success', 'Producto registrado correctamente.');
    }

    public function edit(Producto $producto)
    {
        $categorias = Categoria::orderBy('nombre')->get();
        $unidades = UnidadMedida::orderBy('nombre')->get();

        return view('admin.productos.edit', compact('producto', 'categorias', 'unidades'));
    }

    public function update(Request $request, Producto $producto)
    {
        $data = $this->validateData($request, $producto->id);

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        } else {
            unset($data['imagen']);
        }

        $data['precio_venta'] = $this->calcularPrecioVenta(array_merge($producto->toArray(), $data));

        $producto->update($data);

        return redirect()->route('admin.productos.index')->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        $producto->update(['activo' => false]);

        return redirect()->route('admin.productos.index')->with('success', 'Producto desactivado correctamente.');
    }

    public function toggle(Producto $producto)
    {
        $producto->update(['activo' => ! $producto->activo]);

        return back()->with('success', $producto->activo ? 'Producto activado.' : 'Producto desactivado.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'codigo' => ['nullable', 'string', 'max:50', 'unique:productos,codigo' . ($ignoreId ? ",{$ignoreId}" : '')],
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'imagen' => ['nullable', 'image', 'max:2048'],
            'categoria_id' => ['nullable', 'exists:categorias,id'],
            'unidad_medida_id' => ['nullable', 'exists:unidades_medida,id'],
            'costo_compra' => ['required', 'numeric', 'min:0'],
            'costo_flete' => ['required', 'numeric', 'min:0'],
            'margen_ganancia' => ['required', 'numeric', 'min:0', 'max:999'],
            'precio_venta' => ['nullable', 'numeric', 'min:0'],
            'stock_minimo' => ['required', 'integer', 'min:0'],
            'activo' => ['nullable', 'boolean'],
        ], [], [
            'categoria_id' => 'categoría',
            'unidad_medida_id' => 'unidad de medida',
            'costo_compra' => 'costo de compra',
            'costo_flete' => 'costo de flete',
            'margen_ganancia' => 'margen de ganancia',
            'precio_venta' => 'precio de venta',
            'stock_minimo' => 'stock mínimo',
        ]);
    }

    private function calcularPrecioVenta(array $data): float
    {
        // Si el usuario envió precio_venta manual, respetarlo; si no, calcular por fórmula CCOLCAS
        if (isset($data['precio_venta']) && (float) $data['precio_venta'] > 0) {
            return round((float) $data['precio_venta'], 2);
        }

        $costo = (float) ($data['costo_compra'] ?? 0) + (float) ($data['costo_flete'] ?? 0);
        $margen = (float) ($data['margen_ganancia'] ?? 0);

        return round($costo * (1 + $margen / 100), 2);
    }
}
