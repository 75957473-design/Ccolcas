<?php

namespace App\Services;

class SeleccionProductoService
{
    /**
     * Agrega un producto al pedido si tiene stock suficiente.
     * Lanza excepción si no hay stock.
     */
    public function seleccionarProducto(array $producto, int $cantidad): array
    {
        if (! $this->validarStock($producto['stock'], $cantidad)) {
            throw new \InvalidArgumentException('La cantidad solicitada supera el stock disponible.');
        }

        return [
            'id'       => $producto['id'],
            'nombre'   => $producto['nombre'],
            'precio'   => $producto['precio'],
            'cantidad' => $cantidad,
            'subtotal' => round($producto['precio'] * $cantidad, 2),
        ];
    }

    /**
     * Valida que la cantidad solicitada no supere el stock disponible.
     */
    public function validarStock(int $stock, int $cantidad): bool
    {
        return $cantidad > 0 && $cantidad <= $stock;
    }

    /**
     * Modifica la cantidad de un producto ya seleccionado.
     * Lanza excepción si la nueva cantidad supera el stock.
     */
    public function modificarCantidad(array $itemSeleccionado, int $stock, int $nuevaCantidad): array
    {
        if (! $this->validarStock($stock, $nuevaCantidad)) {
            throw new \InvalidArgumentException('La nueva cantidad supera el stock disponible.');
        }

        $itemSeleccionado['cantidad'] = $nuevaCantidad;
        $itemSeleccionado['subtotal'] = round($itemSeleccionado['precio'] * $nuevaCantidad, 2);

        return $itemSeleccionado;
    }

    /**
     * Elimina un producto del pedido por su ID.
     */
    public function eliminarProducto(array $items, int $productoId): array
    {
        return array_values(array_filter($items, function ($item) use ($productoId) {
            return $item['id'] !== $productoId;
        }));
    }

    /**
     * Devuelve el resumen del pedido: items y total.
     */
    public function mostrarResumen(array $items): array
    {
        $total = 0;

        foreach ($items as $item) {
            $total += $item['subtotal'];
        }

        return [
            'items' => $items,
            'total' => round($total, 2),
        ];
    }
}