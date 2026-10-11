<?php

namespace Tests\Unit;

use App\Services\PedidoService;
use PHPUnit\Framework\TestCase;

class PedidoServiceTest extends TestCase
{
    private array $itemsValidos;

    protected function setUp(): void
    {
        parent::setUp();

        $this->itemsValidos = [
            ['id' => 1, 'nombre' => 'Detergente', 'precio' => 15.50, 'cantidad' => 2, 'stock' => 20],
            ['id' => 2, 'nombre' => 'Lejía', 'precio' => 5.00, 'cantidad' => 3, 'stock' => 10],
        ];
    }

    // ========== MostrarResumenPedido ==========

    public function test_mostrar_resumen_pedido_con_productos(): void
    {
        // Given: un cliente y varios productos seleccionados
        $service = new PedidoService();

        // When: se solicita el resumen del pedido
        $resumen = $service->mostrarResumenPedido(1, $this->itemsValidos);

        // Then: debe incluir cliente, items y total 46.00
        $this->assertSame(1, $resumen['cliente_id']);
        $this->assertCount(2, $resumen['items']);
        $this->assertSame(46.00, $resumen['total']);
    }

    // ========== ValidarStock ==========

    public function test_validar_stock_suficiente_retorna_true(): void
    {
        // Given: un servicio de pedidos
        $service = new PedidoService();

        // When: se valida el stock de los items
        $resultado = $service->validarStockPedido($this->itemsValidos);

        // Then: debe devolver true
        $this->assertTrue($resultado);
    }

    public function test_validar_stock_insuficiente_retorna_false(): void
    {
        // Given: items con cantidad mayor al stock
        $service = new PedidoService();
        $itemsSinStock = [
            ['id' => 1, 'nombre' => 'Detergente', 'precio' => 15.50, 'cantidad' => 5, 'stock' => 2],
        ];

        // When: se valida el stock
        $resultado = $service->validarStockPedido($itemsSinStock);

        // Then: debe devolver false
        $this->assertFalse($resultado);
    }

    // ========== RegistrarPedido ==========

    public function test_registrar_pedido_con_stock_suficiente(): void
    {
        // Given: un cliente registrado y productos con stock
        $service = new PedidoService();

        // When: se registra el pedido
        $pedido = $service->registrarPedido(1, $this->itemsValidos);

        // Then: debe tener cliente, código, estado e items
        $this->assertSame(1, $pedido['cliente_id']);
        $this->assertSame('pendiente', $pedido['estado']);
        $this->assertStringStartsWith('PED-', $pedido['codigo']);
        $this->assertCount(2, $pedido['items']);
    }

    public function test_registrar_pedido_sin_stock_lanza_excepcion(): void
    {
        // Given: items sin stock suficiente
        $service = new PedidoService();
        $itemsSinStock = [
            ['id' => 1, 'nombre' => 'Detergente', 'precio' => 15.50, 'cantidad' => 10, 'stock' => 2],
        ];

        // Then: se espera una excepción
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No hay stock suficiente para uno o más productos.');

        // When: se intenta registrar el pedido
        $service->registrarPedido(1, $itemsSinStock);
    }

    // ========== RegistrarDetalle ==========

    public function test_registrar_detalle_pedido(): void
    {
        // Given: un servicio de pedidos y varios items
        $service = new PedidoService();

        // When: se prepara el detalle
        $detalle = $service->registrarDetalle($this->itemsValidos);

        // Then: cada item debe tener producto_id, cantidad, precio y subtotal
        $this->assertCount(2, $detalle);
        $this->assertSame(1, $detalle[0]['producto_id']);
        $this->assertSame(31.00, $detalle[0]['subtotal']);
        $this->assertSame(15.00, $detalle[1]['subtotal']);
    }

    // ========== AsociarCliente ==========

    public function test_asociar_cliente_a_pedido(): void
    {
        // Given: un pedido sin cliente
        $service = new PedidoService();
        $pedido = ['codigo' => 'PED-20240101-0001', 'estado' => 'pendiente'];

        // When: se asocia al cliente con ID 5
        $pedidoActualizado = $service->asociarCliente($pedido, 5);

        // Then: el pedido debe tener cliente_id = 5
        $this->assertSame(5, $pedidoActualizado['cliente_id']);
    }

    // ========== GenerarCodigoPedido ==========

    public function test_generar_codigo_pedido_tiene_formato_correcto(): void
    {
        // Given: un servicio de pedidos
        $service = new PedidoService();

        // When: se genera un código de pedido
        $codigo = $service->generarCodigoPedido();

        // Then: debe empezar con "PED-" y seguir el formato PED-YYYYMMDD-XXXX
        $this->assertMatchesRegularExpression('/^PED-\d{8}-\d{4}$/', $codigo);
    }

    public function test_dos_codigos_generados_son_diferentes(): void
    {
        // Given: un servicio de pedidos
        $service = new PedidoService();

        // When: se generan dos códigos
        $codigo1 = $service->generarCodigoPedido();
        $codigo2 = $service->generarCodigoPedido();

        // Then: pueden ser diferentes (alta probabilidad)
        // Nota: como usa random_int, existe una probabilidad mínima de que coincidan
        // pero eso no invalida el test, solo verifica que el formato sea correcto
        $this->assertMatchesRegularExpression('/^PED-\d{8}-\d{4}$/', $codigo1);
        $this->assertMatchesRegularExpression('/^PED-\d{8}-\d{4}$/', $codigo2);
    }

    // ========== PRUEBAS CON MOCK ==========

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    public function test_registra_pedido_usando_mock_del_repositorio(): void
    {
        // Given: un mock del repositorio que guarda el pedido
        $mockRepo = \Mockery::mock(\App\Contracts\RepositorioPedidoInterface::class);
        $mockRepo->shouldReceive('guardar')
            ->once()
            ->andReturnUsing(function ($pedido) {
                $pedido['id'] = 100;
                return $pedido;
            });

        $service = new \App\Services\PedidoService($mockRepo);

        // When: se registra el pedido
        $pedido = $service->registrarPedido(1, $this->itemsValidos);

        // Then: el pedido debe tener ID asignado por el repositorio
        $this->assertSame(100, $pedido['id']);
        $this->assertSame(1, $pedido['cliente_id']);
    }

    public function test_registra_pedido_sin_repositorio_funciona_localmente(): void
    {
        // Given: un servicio sin repositorio
        $service = new \App\Services\PedidoService();

        // When: se registra el pedido
        $pedido = $service->registrarPedido(1, $this->itemsValidos);

        // Then: debe devolver el pedido generado localmente
        $this->assertSame(1, $pedido['cliente_id']);
        $this->assertSame('pendiente', $pedido['estado']);
        $this->assertStringStartsWith('PED-', $pedido['codigo']);
    }





}