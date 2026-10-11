<?php

namespace Tests\Unit;

use App\Services\InventarioService;
use PHPUnit\Framework\TestCase;

class InventarioServiceTest extends TestCase
{
    private array $productos;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productos = [
            ['id' => 1, 'nombre' => 'Detergente', 'stock' => 10],
            ['id' => 2, 'nombre' => 'Lejía', 'stock' => 20],
            ['id' => 3, 'nombre' => 'Desinfectante', 'stock' => 5],
        ];
    }

    // ========== MostrarInventario ==========

    public function test_mostrar_inventario_completo(): void
    {
        // Given: un inventario con 3 productos
        $service = new InventarioService();

        // When: se muestra el inventario
        $resultado = $service->mostrarInventario($this->productos);

        // Then: debe mostrar los 3 productos con su stock
        $this->assertCount(3, $resultado);
        $this->assertSame(10, $resultado[0]['stock']);
        $this->assertSame(20, $resultado[1]['stock']);
        $this->assertSame(5, $resultado[2]['stock']);
    }

    // ========== RegistrarEntrada ==========

    public function test_registrar_entrada_actualiza_stock(): void
    {
        // Given: un servicio de inventario y un producto con stock 10
        $service = new InventarioService();

        // When: se registra una entrada de 5 unidades
        $nuevoStock = $service->registrarEntrada(10, 5);

        // Then: el stock debe ser 15
        $this->assertSame(15, $nuevoStock);
    }

    public function test_registrar_entrada_cantidad_cero_lanza_excepcion(): void
    {
        // Given: un servicio de inventario
        $service = new InventarioService();

        // Then: se espera una excepción
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La cantidad de entrada debe ser mayor que cero.');

        // When: se intenta registrar entrada con cantidad 0
        $service->registrarEntrada(10, 0);
    }

    // ========== RegistrarSalida ==========

    public function test_registrar_salida_actualiza_stock(): void
    {
        // Given: un servicio de inventario y un producto con stock 20
        $service = new InventarioService();

        // When: se registra una salida de 5 unidades
        $nuevoStock = $service->registrarSalida(20, 5);

        // Then: el stock debe ser 15
        $this->assertSame(15, $nuevoStock);
    }

    public function test_registrar_salida_exacta_actualiza_a_cero(): void
    {
        // Given: un servicio de inventario y un producto con stock 5
        $service = new InventarioService();

        // When: se registra una salida de 5 unidades (todo el stock)
        $nuevoStock = $service->registrarSalida(5, 5);

        // Then: el stock debe ser 0
        $this->assertSame(0, $nuevoStock);
    }

    public function test_registrar_salida_superior_al_stock_lanza_excepcion(): void
    {
        // Given: un servicio de inventario y un producto con stock 2
        $service = new InventarioService();

        // Then: se espera una excepción
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La cantidad de salida supera el stock disponible.');

        // When: se intenta registrar salida de 5 unidades
        $service->registrarSalida(2, 5);
    }

    public function test_registrar_salida_cantidad_cero_lanza_excepcion(): void
    {
        // Given: un servicio de inventario
        $service = new InventarioService();

        // Then: se espera una excepción
        $this->expectException(\InvalidArgumentException::class);

        // When: se intenta registrar salida con cantidad 0
        $service->registrarSalida(10, 0);
    }

    // ========== ValidarStock ==========

    public function test_validar_stock_suficiente_retorna_true(): void
    {
        // Given: un servicio de inventario
        $service = new InventarioService();

        // When: se valida stock 10 con salida 5
        $resultado = $service->validarStock(10, 5);

        // Then: debe devolver true
        $this->assertTrue($resultado);
    }

    public function test_validar_stock_insuficiente_retorna_false(): void
    {
        // Given: un servicio de inventario
        $service = new InventarioService();

        // When: se valida stock 2 con salida 5
        $resultado = $service->validarStock(2, 5);

        // Then: debe devolver false
        $this->assertFalse($resultado);
    }

    public function test_validar_stock_exacto_retorna_true(): void
    {
        // Given: un servicio de inventario
        $service = new InventarioService();

        // When: se valida stock 5 con salida 5
        $resultado = $service->validarStock(5, 5);

        // Then: debe devolver true
        $this->assertTrue($resultado);
    }

    // ========== ActualizarInventario ==========

    public function test_actualizar_inventario_entrada(): void
    {
        // Given: un servicio de inventario
        $service = new InventarioService();

        // When: se actualiza con movimiento "entrada" de 3
        $nuevoStock = $service->actualizarInventario(10, 'entrada', 3);

        // Then: el stock debe ser 13
        $this->assertSame(13, $nuevoStock);
    }

    public function test_actualizar_inventario_salida(): void
    {
        // Given: un servicio de inventario
        $service = new InventarioService();

        // When: se actualiza con movimiento "salida" de 4
        $nuevoStock = $service->actualizarInventario(10, 'salida', 4);

        // Then: el stock debe ser 6
        $this->assertSame(6, $nuevoStock);
    }

    public function test_actualizar_inventario_tipo_invalido_lanza_excepcion(): void
    {
        // Given: un servicio de inventario
        $service = new InventarioService();

        // Then: se espera una excepción
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tipo de movimiento no válido.');

        // When: se intenta con un tipo desconocido
        $service->actualizarInventario(10, 'ajuste', 5);
    }

    // ========== ConsultarStock ==========

    public function test_consultar_stock_de_producto(): void
    {
        // Given: un servicio de inventario y un producto con stock 10
        $service = new InventarioService();

        // When: se consulta el stock
        $stock = $service->consultarStock($this->productos[0]);

        // Then: debe devolver 10
        $this->assertSame(10, $stock);
    }

    public function test_consultar_stock_de_producto_sin_stock_definido(): void
    {
        // Given: un producto sin clave "stock"
        $service = new InventarioService();

        // When: se consulta el stock
        $stock = $service->consultarStock(['id' => 99, 'nombre' => 'Producto nuevo']);

        // Then: debe devolver 0
        $this->assertSame(0, $stock);
    }

    // ========== PRUEBAS CON MOCK ==========

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    public function test_actualiza_stock_en_repositorio_con_mock(): void
    {
        // Given: un mock del repositorio que confirma la actualización
        $mockRepo = \Mockery::mock(\App\Contracts\RepositorioInventarioInterface::class);
        $mockRepo->shouldReceive('actualizarStock')
            ->once()
            ->with(1, 15)
            ->andReturn(true);

        $service = new InventarioService($mockRepo);

        // When: se actualiza el stock del producto 1 a 15
        $resultado = $service->actualizarStockEnRepositorio(1, 15);

        // Then: debe devolver true
        $this->assertTrue($resultado);
    }

    public function test_no_permite_actualizar_sin_repositorio(): void
    {
        // Given: un servicio sin repositorio
        $service = new InventarioService();

        // Then: se espera una excepción
        $this->expectException(\RuntimeException::class);

        // When: se intenta actualizar stock
        $service->actualizarStockEnRepositorio(1, 15);
    }
}