<?php

namespace App\Services;

use InvalidArgumentException;

class ServicioCarritoCompras
{
	public function mostrarResumenPedido(array $cliente, array $productos): array
	{
		$this->validarProductos($productos);

		$total = array_reduce($productos, function (float $acumulado, array $producto): float {
			return $acumulado + ((float) $producto['precio'] * (int) $producto['cantidad']);
		}, 0.0);

		return [
			'cliente' => $cliente,
			'productos' => $productos,
			'total' => round($total, 2),
		];
	}

	public function validarStock(array $productos, array $stockDisponible): bool
	{
		$this->validarProductos($productos);

		foreach ($productos as $producto) {
			$productoId = $producto['producto_id'];
			$cantidadDisponible = $stockDisponible[$productoId] ?? null;

			if (!is_numeric($cantidadDisponible) || (int) $cantidadDisponible < (int) $producto['cantidad']) {
				throw new InvalidArgumentException(
					"Stock insuficiente para el producto {$productoId}."
				);
			}
		}

		return true;
	}

	public function registrarPedido(array $cliente, array $productos, array $stockDisponible, array &$pedidos): array
	{
		if (!isset($cliente['id']) || !is_numeric($cliente['id']) || (int) $cliente['id'] < 1) {
			throw new InvalidArgumentException('El pedido requiere un cliente identificado.');
		}

		$this->validarStock($productos, $stockDisponible);
		$resumen = $this->mostrarResumenPedido($cliente, $productos);
		$codigo = $this->generarCodigoPedido();

		$pedido = $this->asociarCliente([
			'codigo' => $codigo,
			'total' => $resumen['total'],
			'detalles' => $this->registrarDetalle($productos),
		], $cliente);

		$pedidos[] = $pedido;

		return [
			'registrado' => true,
			'codigo' => $codigo,
			'pedido' => $pedido,
		];
	}

	public function registrarDetalle(array $productos): array
	{
		$this->validarProductos($productos);

		return array_map(function (array $producto): array {
			$precio = (float) $producto['precio'];
			$cantidad = (int) $producto['cantidad'];

			return [
				'producto_id' => (int) $producto['producto_id'],
				'cantidad' => $cantidad,
				'precio_unitario' => $precio,
				'subtotal' => round($precio * $cantidad, 2),
			];
		}, $productos);
	}

	public function asociarCliente(array $pedido, array $cliente): array
	{
		if (!isset($cliente['id']) || !is_numeric($cliente['id']) || (int) $cliente['id'] < 1) {
			throw new InvalidArgumentException('El pedido requiere un cliente identificado.');
		}

		$pedido['cliente_id'] = (int) $cliente['id'];

		return $pedido;
	}

	public function generarCodigoPedido(): string
	{
		return 'PED-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
	}

	private function validarProductos(array $productos): void
	{
		if ($productos === []) {
			throw new InvalidArgumentException('El pedido debe incluir al menos un producto.');
		}

		foreach ($productos as $producto) {
			if (!isset($producto['producto_id'], $producto['cantidad'], $producto['precio'])
				|| !is_numeric($producto['producto_id'])
				|| !is_numeric($producto['cantidad'])
				|| !is_numeric($producto['precio'])
				|| (int) $producto['producto_id'] < 1
				|| (int) $producto['cantidad'] < 1
				|| (float) $producto['precio'] < 0) {
				throw new InvalidArgumentException('Los productos del pedido contienen datos inválidos.');
			}
		}
	}
}
