<?php

namespace App\Contracts;

interface ServicioNotificacionInterface
{
    /**
     * Envía una notificación de confirmación al cliente.
     */
    public function notificarConfirmacion(string $destinatario, string $codigoPedido): bool;
}