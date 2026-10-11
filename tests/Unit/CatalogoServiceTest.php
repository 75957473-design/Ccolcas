<?php

namespace Tests\Unit;

use App\Services\CatalogoService;
use PHPUnit\Framework\TestCase;

class CatalogoServiceTest extends TestCase
{
    private array $productos;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productos = [
            [
                'id' => 1, 'nombre' => 'Detergente Limón', 'categoria' => 'Detergentes',
                'caracteristicas' => 'Rinde 50 lavadas', 'precio' => 15.50, 'stock' => 20, 'activo' => true,
            ],
            [
                'id' => 2, 'nombre' => 'Detergente Flor', 'categoria' => 'Detergentes',
                'caracteristicas' => 'Aroma floral', 'precio' => 16.00, 'stock' => 10, 'activo' => true,
            ],
            [
                'id' => 3, 'nombre' => 'Lejía Clásica', 'categoria' => 'Desinfectantes',
                'caracteristicas' => 'Desinfecta y blanquea', 'precio' => 5.00, 'stock' => 0, 'activo' => true,
            ],
            [
                'id' => 4, 'nombre' => 'Desinfectante Pino', 'categoria' => 'Desinfectantes',
                'caracteristicas' => 'Aroma pino', 'precio' => 8.50, 'stock' => 15, 'activo' => true,
            ],
            [
                'id' => 5, 'nombre' => 'Limpiador descontinuado', 'categoria' => 'Otros',
                'caracteristicas' => '', 'precio' => 3.00, 'stock' => 5, 'activo' => false,
            ],
        ];
    }

    // ========== MostrarCatalogo ==========

    public function test_muestra_solo_productos_activos(): void
    {
        // Given: un catálogo con 5 productos, uno inactivo
        $service = new CatalogoService();

        // When: se filtran los productos disponibles
        $resultado = $service->productosDisponibles($this->productos);

        // Then: debe haber 4 productos activos
        $this->assertCount(4, $resultado);
        foreach ($resultado as $producto) {
            $this->assertTrue($producto['activo']);
        }
    }

    // ========== BuscarProducto por nombre ==========

    public function test_buscar_producto_por_nombre_detergente(): void
    {
        // Given: un catálogo de productos
        $service = new CatalogoService();

        // When: se busca "Detergente"
        $resultado = $service->buscarPorNombre($this->productos, 'Detergente');

        // Then: debe devolver 2 productos
        $this->assertCount(2, $resultado);
        $this->assertSame('Detergente Limón', $resultado[0]['nombre']);
    }

    public function test_buscar_producto_sin_resultados(): void
    {
        // Given: un catálogo de productos
        $service = new CatalogoService();

        // When: se busca algo que no existe
        $resultado = $service->buscarPorNombre($this->productos, 'Jabón');

        // Then: debe devolver un arreglo vacío
        $this->assertEmpty($resultado);
    }

    // ========== BuscarCategoria ==========

    public function test_buscar_productos_por_categoria_desinfectantes(): void
    {
        // Given: un catálogo de productos
        $service = new CatalogoService();

        // When: se buscan productos de la categoría "Desinfectantes"
        $resultado = $service->buscarPorCategoria($this->productos, 'Desinfectantes');

        // Then: debe devolver 2 productos
        $this->assertCount(2, $resultado);
    }

    public function test_buscar_categoria_inexistente(): void
    {
        // Given: un catálogo de productos
        $service = new CatalogoService();

        // When: se busca una categoría que no existe
        $resultado = $service->buscarPorCategoria($this->productos, 'Aromatizantes');

        // Then: debe devolver vacío
        $this->assertEmpty($resultado);
    }

    // ========== MostrarStock ==========

    public function test_producto_con_stock_tiene_disponibilidad(): void
    {
        // Given: un producto con stock 20
        $service = new CatalogoService();

        // When: se verifica si tiene stock
        $resultado = $service->tieneStock($this->productos[0]);

        // Then: debe devolver true
        $this->assertTrue($resultado);
    }

    public function test_producto_sin_stock_no_tiene_disponibilidad(): void
    {
        // Given: un producto con stock 0
        $service = new CatalogoService();

        // When: se verifica si tiene stock
        $resultado = $service->tieneStock($this->productos[2]);

        // Then: debe devolver false
        $this->assertFalse($resultado);
    }

    // ========== MostrarProducto ==========

    public function test_informacion_de_producto_completa(): void
    {
        // Given: un producto registrado
        $service = new CatalogoService();

        // When: se solicita su información
        $info = $service->informacionProducto($this->productos[0]);

        // Then: debe incluir nombre, precio, stock y disponibilidad
        $this->assertSame('Detergente Limón', $info['nombre']);
        $this->assertSame(15.50, $info['precio']);
        $this->assertSame(20, $info['stock']);
        $this->assertTrue($info['disponible']);
    }

    // ========== ConsultarProducto ==========

    public function test_consultar_producto_existente_por_id(): void
    {
        // Given: un catálogo de productos
        $service = new CatalogoService();

        // When: se consulta el producto con ID 3
        $producto = $service->consultarPorId($this->productos, 3);

        // Then: debe devolver el producto correcto
        $this->assertNotNull($producto);
        $this->assertSame('Lejía Clásica', $producto['nombre']);
    }

    public function test_consultar_producto_inexistente_devuelve_null(): void
    {
        // Given: un catálogo de productos
        $service = new CatalogoService();

        // When: se consulta un ID que no existe
        $producto = $service->consultarPorId($this->productos, 999);

        // Then: debe devolver null
        $this->assertNull($producto);
    }

    // ========== PRUEBAS CON MOCK ==========

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    public function test_obtiene_catalogo_con_mock(): void
    {
        // Given: un mock del repositorio que devuelve 2 productos
        $mockRepo = \Mockery::mock(\App\Contracts\RepositorioProductoInterface::class);
        $mockRepo->shouldReceive('obtenerProductosActivos')
            ->once()
            ->andReturn([
                ['id' => 1, 'nombre' => 'Detergente', 'precio' => 15.50, 'stock' => 10, 'activo' => true],
                ['id' => 2, 'nombre' => 'Lejía', 'precio' => 5.00, 'stock' => 20, 'activo' => true],
            ]);

        $service = new \App\Services\CatalogoService($mockRepo);

        // When: se obtiene el catálogo
        $resultado = $service->obtenerCatalogo();

        // Then: debe devolver los 2 productos
        $this->assertCount(2, $resultado);
        $this->assertSame('Detergente', $resultado[0]['nombre']);
    }

    public function test_consulta_producto_por_id_con_mock(): void
    {
        // Given: un mock que devuelve un producto específico
        $mockRepo = \Mockery::mock(\App\Contracts\RepositorioProductoInterface::class);
        $mockRepo->shouldReceive('buscarPorId')
            ->once()
            ->with(5)
            ->andReturn([
                'id' => 5, 'nombre' => 'Desinfectante Pino',
                'precio' => 8.50, 'stock' => 15, 'activo' => true,
            ]);

        $service = new \App\Services\CatalogoService($mockRepo);

        // When: se consulta el producto con ID 5
        $producto = $service->consultarProducto(5);

        // Then: debe devolver el producto correcto
        $this->assertNotNull($producto);
        $this->assertSame('Desinfectante Pino', $producto['nombre']);
    }

    public function test_no_permite_obtener_catalogo_sin_repositorio(): void
    {
        // Given: un servicio sin repositorio
        $service = new \App\Services\CatalogoService();

        // Then: se espera una excepción
        $this->expectException(\RuntimeException::class);

        // When: se intenta obtener el catálogo
        $service->obtenerCatalogo();
    }





}