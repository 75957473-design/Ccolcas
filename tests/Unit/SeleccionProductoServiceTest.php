<?php

namespace Tests\Unit;

use App\Services\SeleccionProductoService;
use PHPUnit\Framework\TestCase;

class SeleccionProductoServiceTest extends TestCase
{
    private array $producto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->producto = [
            'id' => 1,
            'nombre' => 'Detergente Limón',
            'precio' => 15.50,
            'stock' => 20,
            'activo' => true,
        ];
    }

    // ========== SeleccionarProducto ==========

    public function test_seleccionar_producto_con_stock_suficiente(): void
    {
        // Given: un producto con stock 20
        $service = new SeleccionProductoService();

        // When: se selecciona con cantidad 3
        $item = $service->seleccionarProducto($this->producto, 3);

        // Then: el item debe tener cantidad 3 y subtotal 46.50
        $this->assertSame(3, $item['cantidad']);
        $this->assertSame(46.50, $item['subtotal']);
    }

    public function test_seleccionar_producto_sin_stock_suficiente_lanza_excepcion(): void
    {
        // Given: un producto con stock 2
        $productoConPocoStock = array_merge($this->producto, ['stock' => 2]);
        $service = new SeleccionProductoService();

        // Then: se espera una excepción
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La cantidad solicitada supera el stock disponible.');

        // When: se intenta seleccionar cantidad 5
        $service->seleccionarProducto($productoConPocoStock, 5);
    }

    // ========== ValidarStock ==========

    public function test_validar_stock_suficiente_retorna_true(): void
    {
        // Given: un servicio de selección
        $service = new SeleccionProductoService();

        // When: se valida stock 5 con cantidad 3
        $resultado = $service->validarStock(5, 3);

        // Then: debe devolver true
        $this->assertTrue($resultado);
    }

    public function test_validar_stock_insuficiente_retorna_false(): void
    {
        // Given: un servicio de selección
        $service = new SeleccionProductoService();

        // When: se valida stock 2 con cantidad 5
        $resultado = $service->validarStock(2, 5);

        // Then: debe devolver false
        $this->assertFalse($resultado);
    }

    public function test_validar_cantidad_cero_retorna_false(): void
    {
        // Given: un servicio de selección
        $service = new SeleccionProductoService();

        // When: se valida cantidad 0
        $resultado = $service->validarStock(10, 0);

        // Then: debe devolver false
        $this->assertFalse($resultado);
    }

    public function test_validar_stock_exacto_retorna_true(): void
    {
        // Given: un servicio de selección
        $service = new SeleccionProductoService();

        // When: se valida stock 5 con cantidad 5 (caso límite)
        $resultado = $service->validarStock(5, 5);

        // Then: debe devolver true
        $this->assertTrue($resultado);
    }

    // ========== ModificarCantidad ==========

    public function test_modificar_cantidad_actualiza_subtotal(): void
    {
        // Given: un item ya seleccionado con cantidad 3
        $service = new SeleccionProductoService();
        $item = [
            'id' => 1, 'nombre' => 'Detergente Limón', 'precio' => 15.50,
            'cantidad' => 3, 'subtotal' => 46.50,
        ];

        // When: se modifica la cantidad a 4
        $itemModificado = $service->modificarCantidad($item, 20, 4);

        // Then: debe actualizar cantidad y subtotal
        $this->assertSame(4, $itemModificado['cantidad']);
        $this->assertSame(62.00, $itemModificado['subtotal']);
    }

    public function test_modificar_cantidad_superior_al_stock_lanza_excepcion(): void
    {
        // Given: un item con cantidad 3
        $service = new SeleccionProductoService();
        $item = [
            'id' => 1, 'nombre' => 'Detergente Limón', 'precio' => 15.50,
            'cantidad' => 3, 'subtotal' => 46.50,
        ];

        // Then: se espera una excepción
        $this->expectException(\InvalidArgumentException::class);

        // When: se intenta modificar a cantidad 10 con stock 5
        $service->modificarCantidad($item, 5, 10);
    }

    // ========== EliminarProducto ==========

    public function test_eliminar_producto_del_pedido(): void
    {
        // Given: un pedido con 2 productos
        $service = new SeleccionProductoService();
        $items = [
            ['id' => 1, 'nombre' => 'Detergente', 'precio' => 15.50, 'cantidad' => 2, 'subtotal' => 31.00],
            ['id' => 2, 'nombre' => 'Lejía', 'precio' => 5.00, 'cantidad' => 3, 'subtotal' => 15.00],
        ];

        // When: se elimina el producto con ID 1
        $itemsActualizados = $service->eliminarProducto($items, 1);

        // Then: solo debe quedar el producto con ID 2
        $this->assertCount(1, $itemsActualizados);
        $this->assertSame(2, $itemsActualizados[0]['id']);
    }

    public function test_eliminar_producto_inexistente_no_cambia_nada(): void
    {
        // Given: un pedido con 1 producto
        $service = new SeleccionProductoService();
        $items = [
            ['id' => 1, 'nombre' => 'Detergente', 'precio' => 15.50, 'cantidad' => 2, 'subtotal' => 31.00],
        ];

        // When: se intenta eliminar un ID que no existe
        $itemsActualizados = $service->eliminarProducto($items, 999);

        // Then: no debe cambiar nada
        $this->assertCount(1, $itemsActualizados);
    }

    // ========== MostrarResumen ==========

    public function test_mostrar_resumen_con_varios_productos(): void
    {
        // Given: un pedido con 2 productos
        $service = new SeleccionProductoService();
        $items = [
            ['id' => 1, 'nombre' => 'Detergente', 'precio' => 15.50, 'cantidad' => 2, 'subtotal' => 31.00],
            ['id' => 2, 'nombre' => 'Lejía', 'precio' => 5.00, 'cantidad' => 3, 'subtotal' => 15.00],
        ];

        // When: se solicita el resumen
        $resumen = $service->mostrarResumen($items);

        // Then: debe devolver los items y el total 46.00
        $this->assertCount(2, $resumen['items']);
        $this->assertSame(46.00, $resumen['total']);
    }

    public function test_mostrar_resumen_vacio(): void
    {
        // Given: un pedido vacío
        $service = new SeleccionProductoService();

        // When: se solicita el resumen
        $resumen = $service->mostrarResumen([]);

        // Then: el total debe ser 0
        $this->assertSame(0.0, $resumen['total']);
        $this->assertEmpty($resumen['items']);
    }
}