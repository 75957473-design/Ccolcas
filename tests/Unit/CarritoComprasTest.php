<?php

namespace Tests\Unit;

use App\Services\ServicioCarritoCompras;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CarritoComprasTest extends TestCase
{
	private function cliente(): array
	{
		return [
			'id' => 7,
			'nombre' => 'María López',
			'email' => 'maria@ccolcas.com',
		];
	}

	private function productosSeleccionados(): array
	{
		return [
			[
				'producto_id' => 12,
				'nombre' => 'Detergente líquido',
				'cantidad' => 2,
				'precio' => 12.50,
			],
			[
				'producto_id' => 18,
				'nombre' => 'Desinfectante',
				'cantidad' => 1,
				'precio' => 4.25,
			],
		];
	}

	private function stockDisponible(): array
	{
		return [
			12 => 5,
			18 => 3,
		];
	}

	public function test_muestra_resumen_del_pedido_con_productos_y_total(): void
	{
		$servicio = new ServicioCarritoCompras();
		$cliente = $this->cliente();
		$productos = $this->productosSeleccionados();

		$resumen = $servicio->mostrarResumenPedido($cliente, $productos);

		$this->assertSame($cliente, $resumen['cliente']);
		$this->assertSame($productos, $resumen['productos']);
		$this->assertSame(29.25, $resumen['total']);
	}

	public function test_permite_continuar_si_hay_stock_suficiente(): void
	{
		$servicio = new ServicioCarritoCompras();

		$resultado = $servicio->validarStock($this->productosSeleccionados(), $this->stockDisponible());

		$this->assertTrue($resultado);
	}

	public function test_rechaza_el_pedido_si_el_stock_es_insuficiente(): void
	{
		$servicio = new ServicioCarritoCompras();
		$stock = $this->stockDisponible();
		$stock[12] = 1;

		$this->expectException(InvalidArgumentException::class);

		$servicio->validarStock($this->productosSeleccionados(), $stock);
	}

	public function test_no_registra_un_pedido_sin_cliente_identificado(): void
	{
		$servicio = new ServicioCarritoCompras();
		$pedidos = [];

		$this->expectException(InvalidArgumentException::class);

		$servicio->registrarPedido([], $this->productosSeleccionados(), $this->stockDisponible(), $pedidos);
	}

	public function test_registra_el_pedido_y_lo_asocia_al_cliente(): void
	{
		$servicio = new ServicioCarritoCompras();
		$pedidos = [];

		$resultado = $servicio->registrarPedido(
			$this->cliente(),
			$this->productosSeleccionados(),
			$this->stockDisponible(),
			$pedidos
		);

		$this->assertTrue($resultado['registrado']);
		$this->assertCount(1, $pedidos);
		$this->assertSame(7, $pedidos[0]['cliente_id']);
		$this->assertSame($resultado['codigo'], $pedidos[0]['codigo']);
	}

	public function test_registra_el_detalle_con_cantidades_y_subtotales(): void
	{
		$servicio = new ServicioCarritoCompras();

		$detalles = $servicio->registrarDetalle($this->productosSeleccionados());

		$this->assertCount(2, $detalles);
		$this->assertSame(12, $detalles[0]['producto_id']);
		$this->assertSame(2, $detalles[0]['cantidad']);
		$this->assertSame(25.00, $detalles[0]['subtotal']);
		$this->assertSame(4.25, $detalles[1]['subtotal']);
	}

	public function test_asocia_el_pedido_al_cliente(): void
	{
		$servicio = new ServicioCarritoCompras();
		$pedido = ['codigo' => 'PED-TEST'];

		$pedidoAsociado = $servicio->asociarCliente($pedido, $this->cliente());

		$this->assertSame(7, $pedidoAsociado['cliente_id']);
	}

	public function test_genera_un_codigo_para_identificar_el_pedido(): void
	{
		$servicio = new ServicioCarritoCompras();

		$codigo = $servicio->generarCodigoPedido();

		$this->assertIsString($codigo);
		$this->assertNotSame('', $codigo);
		$this->assertStringStartsWith('PED-', $codigo);
	}
}
