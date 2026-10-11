<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CarritoController extends Controller
{
    /**
     * Muestra la vista principal del carrito
     */
    public function index()
    {
        $carrito = session()->get('carrito', []);

        if ($carrito !== []) {
            // Consultar las imágenes actuales para corregir URLs antiguas guardadas en sesión.
            $productos = Producto::query()
                ->whereIn('id', array_keys($carrito))
                ->get(['id', 'imagen'])
                ->keyBy('id');

            // Actualizar cada artículo sin modificar sus cantidades ni subtotales.
            foreach ($carrito as $productoId => &$item) {
                $producto = $productos->get($productoId);
                $item['url_imagen'] = $producto?->url_imagen ?? asset('images/producto-default.svg');
            }
            unset($item);

            // Persistir las URLs corregidas para las siguientes visitas al carrito.
            session()->put('carrito', $carrito);
        }

        return view('carrito', compact('carrito'));
    }

    /**
     * Agrega un producto al carrito vía AJAX / Formulario
     */
    public function agregar(Request $request)
    {
        // Validar que el producto exista y que la cantidad, si se envía, sea positiva.
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad'    => 'nullable|integer|min:1'
        ]);

        $producto = Producto::with(['categoria', 'stocks'])->findOrFail($request->producto_id);
        $cantidad = (int) ($request->cantidad ?? 1);

        $carrito = session()->get('carrito', []);
        $cantidadEnCarrito = (int) ($carrito[$producto->id]['cantidad'] ?? 0);

        // La disponibilidad se compara con el total acumulado, no solo con esta solicitud.
        $this->validarStockDisponible($producto, $cantidadEnCarrito + $cantidad);

        if (isset($carrito[$producto->id])) {
            // Si el producto ya está en la sesión, sumar la cantidad y recalcular su subtotal.
            $carrito[$producto->id]['cantidad'] += $cantidad;
            $carrito[$producto->id]['subtotal'] = $carrito[$producto->id]['cantidad'] * $carrito[$producto->id]['precio'];
        } else {
            // Si es nuevo, guardar en sesión los datos necesarios para mostrarlo en el carrito.
            $carrito[$producto->id] = [
                'id'         => $producto->id,
                'nombre'     => $producto->nombre,
                'codigo'     => $producto->codigo,
                'categoria'  => $producto->categoria->nombre ?? 'Limpieza',
                'precio'     => (float) $producto->precio_venta,
                'cantidad'   => $cantidad,
                'subtotal'   => (float) $producto->precio_venta * $cantidad,
                'url_imagen' => $producto->url_imagen,
            ];
        }

        // Mantener la sesión con el carrito actualizado y contar las unidades para la respuesta AJAX.
        session()->put('carrito', $carrito);

        $totalItems = array_sum(array_column($carrito, 'cantidad'));

        if ($request->ajax()) {
            // Las solicitudes AJAX reciben JSON; los formularios tradicionales regresan a la página previa.
            return response()->json([
                'success'    => true,
                'message'    => 'Producto agregado al carrito con éxito',
                'cart_count' => $totalItems
            ]);
        }

        return redirect()->back()->with('success', 'Producto agregado al carrito.');
    }

    /**
     * Actualiza la cantidad de un ítem en el carrito
     */
    public function actualizar(Request $request)
    {
        // La actualización requiere un ID y una cantidad entera mayor o igual que uno.
        $request->validate([
            'producto_id' => 'required|integer',
            'cantidad'    => 'required|integer|min:1'
        ]);

        $carrito = session()->get('carrito', []);

        if (isset($carrito[$request->producto_id])) {
            // Consultar el inventario actual antes de cambiar la cantidad guardada.
            $producto = Producto::with('stocks')->findOrFail($request->producto_id);
            $this->validarStockDisponible($producto, (int) $request->cantidad);

            // Reemplazar la cantidad y recalcular el subtotal con el precio de sesión.
            $carrito[$request->producto_id]['cantidad'] = (int) $request->cantidad;
            $carrito[$request->producto_id]['subtotal'] = $carrito[$request->producto_id]['cantidad'] * $carrito[$request->producto_id]['precio'];

            // Guardar el carrito modificado únicamente si el producto estaba seleccionado.
            session()->put('carrito', $carrito);
        }

        return redirect()->route('carrito.index')->with('success', 'Carrito actualizado correctamente.');
    }

    /**
     * Elimina un producto del carrito
     */
    public function eliminar($id)
    {
        $carrito = session()->get('carrito', []);

        if (isset($carrito[$id])) {
            // Quitar el producto seleccionado y conservar el resto del carrito.
            unset($carrito[$id]);
            session()->put('carrito', $carrito);
        }

        return redirect()->route('carrito.index')->with('success', 'Producto eliminado del carrito.');
    }

    private function validarStockDisponible(Producto $producto, int $cantidadSolicitada): void
    {
        // Rechazar la operación con un error asociado a cantidad si supera el inventario real.
        if ($cantidadSolicitada > $producto->stockTotal()) {
            throw ValidationException::withMessages([
                'cantidad' => "Solo hay {$producto->stockTotal()} unidades disponibles de {$producto->nombre}.",
            ]);
        }
    }
}