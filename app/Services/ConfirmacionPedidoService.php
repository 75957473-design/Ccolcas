<?php

namespace App\Services;

use App\Contracts\ServicioNotificacionInterface;

class ConfirmacionPedidoService
{
    public function __construct(
        private ?ServicioNotificacionInterface $notificador = null
    ) {
    }

    public function confirmarPedido(array $pedido): array
    {
        if (! $this->validarConfirmacion($pedido)) {
            throw new \InvalidArgumentException('El pedido no ha sido registrado correctamente.');
        }

        $confirmacion = [
            'confirmado' => true,
            'mensaje'    => 'Su pedido ha sido registrado correctamente.',
            'codigo'     => $pedido['codigo'],
        ];

        if ($this->notificador !== null && isset($pedido['cliente_correo'])) {
            $this->notificador->notificarConfirmacion(
                $pedido['cliente_correo'],
                $pedido['codigo']
            );
        }

        return $confirmacion;
    }

    public function validarConfirmacion(array $pedido): bool
    {
        return isset($pedido['codigo'])
            && ! empty($pedido['codigo'])
            && isset($pedido['estado'])
            && ! empty($pedido['estado']);
    }

    public function mostrarCodigoPedido(array $pedido): string
    {
        if (! isset($pedido['codigo'])) {
            throw new \InvalidArgumentException('El pedido no tiene código asignado.');
        }

        return $pedido['codigo'];
    }

    public function mostrarDetallePedido(array $pedido): array
    {
        return array_map(function ($item) {
            return [
                'nombre'   => $item['nombre'],
                'cantidad' => $item['cantidad'],
                'subtotal' => $item['subtotal'] ?? round($item['precio'] * $item['cantidad'], 2),
            ];
        }, $pedido['items'] ?? []);
    }

    public function consultarPedido(array $pedidos, string $codigo): ?array
    {
        foreach ($pedidos as $pedido) {
            if ($pedido['codigo'] === $codigo) {
                return $pedido;
            }
        }

        return null;
    }

    public function mensajeConfirmacion(): string
    {
        return 'Su pedido ha sido registrado correctamente.';
    }
}