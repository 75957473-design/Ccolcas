<?php

namespace App\Contracts;

interface RepositorioInventarioInterface
{
    /**
     * Actualiza el stock de un producto en la BD.
     */
    public function actualizarStock(int $productoId, int $nuevoStock): bool;

    /**
     * Guarda un movimiento (entrada o salida).
     */
    public function registrarMovimiento(int $productoId, string $tipo, int $cantidad): bool;
}