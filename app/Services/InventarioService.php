<?php

namespace App\Services;

use App\Contracts\RepositorioInventarioInterface;

class InventarioService
{
    public function __construct(
        private ?RepositorioInventarioInterface $repositorio = null
    ) {
    }

    /**
     * Devuelve los productos con su stock actual.
     */
    public function mostrarInventario(array $productos): array
    {
        return array_map(function ($producto) {
            return [
                'id'     => $producto['id'],
                'nombre' => $producto['nombre'],
                'stock'  => $producto['stock'],
            ];
        }, $productos);
    }

    /**
     * Registra una entrada de productos.
     */
    public function registrarEntrada(int $stockActual, int $cantidad): int
    {
        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('La cantidad de entrada debe ser mayor que cero.');
        }

        return $stockActual + $cantidad;
    }

    /**
     * Registra una salida de productos.
     */
    public function registrarSalida(int $stockActual, int $cantidad): int
    {
        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('La cantidad de salida debe ser mayor que cero.');
        }

        if (! $this->validarStock($stockActual, $cantidad)) {
            throw new \InvalidArgumentException('La cantidad de salida supera el stock disponible.');
        }

        return $stockActual - $cantidad;
    }

    /**
     * Valida que la cantidad a retirar no supere el stock.
     */
    public function validarStock(int $stockActual, int $cantidad): bool
    {
        return $cantidad <= $stockActual;
    }

    /**
     * Actualiza el inventario según el tipo de movimiento.
     */
    public function actualizarInventario(int $stockActual, string $tipo, int $cantidad): int
    {
        return match ($tipo) {
            'entrada' => $this->registrarEntrada($stockActual, $cantidad),
            'salida'  => $this->registrarSalida($stockActual, $cantidad),
            default   => throw new \InvalidArgumentException('Tipo de movimiento no válido.'),
        };
    }

    /**
     * Consulta el stock actualizado de un producto.
     */
    public function consultarStock(array $producto): int
    {
        return $producto['stock'] ?? 0;
    }

    /**
     * Actualiza el stock en el repositorio si está disponible.
     */
    public function actualizarStockEnRepositorio(int $productoId, int $nuevoStock): bool
    {
        if ($this->repositorio === null) {
            throw new \RuntimeException('No hay repositorio de inventario configurado.');
        }

        return $this->repositorio->actualizarStock($productoId, $nuevoStock);
    }
}