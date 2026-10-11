<?php

namespace App\Contracts;

interface RepositorioVentaInterface
{
    /**
     * Guarda una venta y devuelve la venta con su ID asignado.
     */
    public function guardar(array $venta): array;

    /**
     * Devuelve todas las ventas registradas.
     */
    public function obtenerTodas(): array;
}