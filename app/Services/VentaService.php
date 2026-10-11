<?php

namespace App\Services;

use App\Contracts\RepositorioVentaInterface;

class VentaService
{
    public function __construct(
        private ?RepositorioVentaInterface $repositorio = null
    ) {
    }

    /**
     * Devuelve los pedidos disponibles para gestión.
     */
    public function mostrarPedidos(array $pedidos): array
    {
        return array_values(array_filter($pedidos, function ($pedido) {
            return isset($pedido['codigo']) && ! empty($pedido['codigo']);
        }));
    }

    /**
     * Consulta un pedido por su código.
     */
    public function consultarPedido(array $pedidos, string $codigo): ?array
    {
        foreach ($pedidos as $pedido) {
            if ($pedido['codigo'] === $codigo) {
                return $pedido;
            }
        }

        return null;
    }

    /**
     * Registra una venta a partir de un pedido.
     */
    public function registrarVenta(array $pedido, int $vendedorId): array
    {
        if (! isset($pedido['codigo']) || empty($pedido['codigo'])) {
            throw new \InvalidArgumentException('El pedido no está registrado.');
        }

        if (! isset($pedido['items']) || empty($pedido['items'])) {
            throw new \InvalidArgumentException('El pedido no contiene productos.');
        }

        $total = 0;
        foreach ($pedido['items'] as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }

        $venta = [
            'codigo_venta'  => $this->generarCodigoVenta(),
            'pedido_codigo' => $pedido['codigo'],
            'vendedor_id'   => $vendedorId,
            'total'         => round($total, 2),
            'estado'        => 'registrada',
            'fecha'         => date('Y-m-d H:i:s'),
        ];

        if ($this->repositorio !== null) {
            $venta = $this->repositorio->guardar($venta);
        }

        return $venta;
    }

    /**
     * Asocia una venta a un pedido.
     */
    public function asociarVentaPedido(array $venta, string $pedidoCodigo): array
    {
        $venta['pedido_codigo'] = $pedidoCodigo;
        return $venta;
    }

    /**
     * Actualiza el estado de una venta.
     */
    public function actualizarEstadoVenta(array $venta, string $nuevoEstado): array
    {
        $estadosValidos = ['registrada', 'enviada', 'entregada', 'anulada'];

        if (! in_array($nuevoEstado, $estadosValidos, true)) {
            throw new \InvalidArgumentException('Estado de venta no válido.');
        }

        $venta['estado'] = $nuevoEstado;
        return $venta;
    }

    /**
     * Devuelve todas las ventas registradas.
     * Si hay repositorio, lo usa; si no, devuelve el arreglo pasado.
     */
    public function consultarVentas(array $ventas = []): array
    {
        if ($this->repositorio !== null) {
            return $this->repositorio->obtenerTodas();
        }

        return array_values($ventas);
    }

    /**
     * Busca ventas por código de pedido o por nombre de cliente.
     */
    public function buscarVenta(array $ventas, string $criterio): array
    {
        $criterio = strtolower(trim($criterio));

        return array_values(array_filter($ventas, function ($venta) use ($criterio) {
            return str_contains(strtolower($venta['pedido_codigo'] ?? ''), $criterio)
                || str_contains(strtolower($venta['cliente_nombre'] ?? ''), $criterio);
        }));
    }

    /**
     * Genera un código único para la venta.
     */
    public function generarCodigoVenta(): string
    {
        $fecha = date('Ymd');
        $aleatorio = str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);

        return "VEN-{$fecha}-{$aleatorio}";
    }
}