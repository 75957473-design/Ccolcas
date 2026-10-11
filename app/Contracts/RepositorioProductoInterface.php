<?php

namespace App\Contracts;

interface RepositorioProductoInterface
{
    /**
     * Devuelve todos los productos activos.
     */
    public function obtenerProductosActivos(): array;

    /**
     * Busca un producto por su ID.
     */
    public function buscarPorId(int $id): ?array;
}