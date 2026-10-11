<?php

namespace App\Contracts;

interface RepositorioPedidoInterface
{
    /**
     * Guarda un pedido y devuelve el pedido con su código generado.
     */
    public function guardar(array $pedido): array;
}