<?php

namespace App\Services;

use App\Contracts\RepositorioPedidoInterface;

class PedidoService
{
    public function __construct(
        private ?RepositorioPedidoInterface $repositorio = null
    ) {
    }

    /**
     * Valida que todos los items del pedido tengan stock suficiente.
     */
    public function validarStockPedido(array $items): bool
    {
        foreach ($items as $item) {
            if (! isset($item['stock']) || $item['cantidad'] > $item['stock']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Registra un pedido en el repositorio (mock o real).
     */
    public function registrarPedido(int $clienteId, array $items): array
    {
        if (! $this->validarStockPedido($items)) {
            throw new \InvalidArgumentException('No hay stock suficiente para uno o más productos.');
        }

        $pedido = [
            'cliente_id' => $clienteId,
            'codigo'     => $this->generarCodigoPedido(),
            'estado'     => 'pendiente',
            'items'      => $this->registrarDetalle($items),
            'fecha'      => date('Y-m-d H:i:s'),
        ];

        if ($this->repositorio !== null) {
            $pedido = $this->repositorio->guardar($pedido);
        }

        return $pedido;
    }

    /**
     * Prepara los items del detalle del pedido.
     */
    public function registrarDetalle(array $items): array
    {
        return array_map(function ($item) {
            return [
                'producto_id' => $item['id'],
                'nombre'      => $item['nombre'],
                'cantidad'    => $item['cantidad'],
                'precio'      => $item['precio'],
                'subtotal'    => round($item['precio'] * $item['cantidad'], 2),
            ];
        }, $items);
    }

    /**
     * Asocia un pedido a un cliente.
     */
    public function asociarCliente(array $pedido, int $clienteId): array
    {
        $pedido['cliente_id'] = $clienteId;
        return $pedido;
    }

    /**
     * Genera un código único para el pedido.
     */
    public function generarCodigoPedido(): string
    {
        $fecha = date('Ymd');
        $aleatorio = str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);

        return "PED-{$fecha}-{$aleatorio}";
    }

    /**
     * Muestra el resumen del pedido.
     */
    public function mostrarResumenPedido(int $clienteId, array $items): array
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }

        return [
            'cliente_id' => $clienteId,
            'items'      => $items,
            'total'      => round($total, 2),
        ];
    }
}