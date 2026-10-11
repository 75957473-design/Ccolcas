<?php

namespace Tests\Unit;

use App\Services\VentaService;
use PHPUnit\Framework\TestCase;

class VentaServiceTest extends TestCase
{
    private array $pedido;
    private array $venta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pedido = [
            'codigo'     => 'PED-20240101-0001',
            'estado'     => 'pendiente',
            'cliente_id' => 1,
            'items'      => [
                ['nombre' => 'Detergente', 'precio' => 15.50, 'cantidad' => 2],
                ['nombre' => 'Lejía', 'precio' => 5.00, 'cantidad' => 3],
            ],
        ];

        $this->venta = [
            'codigo_venta'   => 'VEN-20240101-0001',
            'pedido_codigo'  => 'PED-20240101-0001',
            'cliente_nombre' => 'Empresa ABC',
            'vendedor_id'    => 2,
            'total'          => 46.00,
            'estado'         => 'registrada',
        ];
    }

    // ========== MostrarPedidos ==========

    public function test_mostrar_pedidos_registrados(): void
    {
        // Given: una lista de pedidos registrados
        $service = new VentaService();
        $pedidos = [
            ['codigo' => 'PED-20240101-0001', 'estado' => 'pendiente'],
            ['codigo' => 'PED-20240101-0002', 'estado' => 'enviado'],
        ];

        // When: se solicitan los pedidos para gestión
        $resultado = $service->mostrarPedidos($pedidos);

        // Then: debe devolver ambos pedidos
        $this->assertCount(2, $resultado);
    }

    public function test_mostrar_pedidos_omite_sin_codigo(): void
    {
        // Given: una lista con un pedido sin código
        $service = new VentaService();
        $pedidos = [
            ['codigo' => 'PED-20240101-0001', 'estado' => 'pendiente'],
            ['codigo' => '', 'estado' => 'pendiente'],
        ];

        // When: se solicitan los pedidos para gestión
        $resultado = $service->mostrarPedidos($pedidos);

        // Then: solo debe devolver 1
        $this->assertCount(1, $resultado);
    }

    // ========== ConsultarPedido ==========

    public function test_consultar_pedido_existente(): void
    {
        // Given: una lista con el pedido registrado
        $service = new VentaService();

        // When: se consulta por el código
        $pedido = $service->consultarPedido([$this->pedido], 'PED-20240101-0001');

        // Then: debe devolver el pedido
        $this->assertNotNull($pedido);
        $this->assertSame(1, $pedido['cliente_id']);
    }

    public function test_consultar_pedido_inexistente_devuelve_null(): void
    {
        // Given: una lista de pedidos
        $service = new VentaService();

        // When: se consulta un código que no existe
        $pedido = $service->consultarPedido([$this->pedido], 'PED-9999-0001');

        // Then: debe devolver null
        $this->assertNull($pedido);
    }

    // ========== RegistrarVenta ==========

    public function test_registrar_venta_con_pedido_valido(): void
    {
        // Given: un pedido registrado y un vendedor
        $service = new VentaService();

        // When: se registra la venta
        $venta = $service->registrarVenta($this->pedido, 2);

        // Then: debe tener código, pedido, vendedor, total y estado
        $this->assertStringStartsWith('VEN-', $venta['codigo_venta']);
        $this->assertSame('PED-20240101-0001', $venta['pedido_codigo']);
        $this->assertSame(2, $venta['vendedor_id']);
        $this->assertSame(46.00, $venta['total']);
        $this->assertSame('registrada', $venta['estado']);
    }

    public function test_registrar_venta_sin_codigo_de_pedido_lanza_excepcion(): void
    {
        // Given: un pedido sin código
        $service = new VentaService();
        $pedidoSinCodigo = ['items' => [['nombre' => 'X', 'precio' => 10, 'cantidad' => 1]]];

        // Then: se espera una excepción
        $this->expectException(\InvalidArgumentException::class);

        // When: se intenta registrar la venta
        $service->registrarVenta($pedidoSinCodigo, 2);
    }

    public function test_registrar_venta_sin_items_lanza_excepcion(): void
    {
        // Given: un pedido sin items
        $service = new VentaService();
        $pedidoSinItems = ['codigo' => 'PED-001', 'items' => []];

        // Then: se espera una excepción
        $this->expectException(\InvalidArgumentException::class);

        // When: se intenta registrar la venta
        $service->registrarVenta($pedidoSinItems, 2);
    }

    // ========== AsociarVentaPedido ==========

    public function test_asociar_venta_a_pedido(): void
    {
        // Given: una venta sin código de pedido
        $service = new VentaService();
        $ventaSinPedido = ['codigo_venta' => 'VEN-001', 'total' => 100];

        // When: se asocia al pedido
        $ventaActualizada = $service->asociarVentaPedido($ventaSinPedido, 'PED-20240101-0001');

        // Then: la venta debe tener pedido_codigo
        $this->assertSame('PED-20240101-0001', $ventaActualizada['pedido_codigo']);
    }

    // ========== ActualizarEstadoVenta ==========

    public function test_actualizar_estado_venta_a_enviada(): void
    {
        // Given: una venta registrada
        $service = new VentaService();

        // When: se actualiza el estado a "enviada"
        $ventaActualizada = $service->actualizarEstadoVenta($this->venta, 'enviada');

        // Then: el estado debe ser "enviada"
        $this->assertSame('enviada', $ventaActualizada['estado']);
    }

    public function test_actualizar_estado_invalido_lanza_excepcion(): void
    {
        // Given: una venta registrada
        $service = new VentaService();

        // Then: se espera una excepción
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Estado de venta no válido.');

        // When: se intenta actualizar a un estado inválido
        $service->actualizarEstadoVenta($this->venta, 'pagada');
    }

    // ========== ConsultarVentas ==========

    public function test_consultar_ventas_registradas(): void
    {
        // Given: una lista de ventas
        $service = new VentaService();
        $ventas = [
            ['codigo_venta' => 'VEN-001', 'total' => 100],
            ['codigo_venta' => 'VEN-002', 'total' => 200],
        ];

        // When: se consultan las ventas
        $resultado = $service->consultarVentas($ventas);

        // Then: debe devolver las 2 ventas
        $this->assertCount(2, $resultado);
    }

    // ========== BuscarVenta ==========

    public function test_buscar_venta_por_codigo_de_pedido(): void
    {
        // Given: una lista de ventas
        $service = new VentaService();
        $ventas = [
            ['codigo_venta' => 'VEN-001', 'pedido_codigo' => 'PED-20240101-0001', 'cliente_nombre' => 'Empresa ABC'],
            ['codigo_venta' => 'VEN-002', 'pedido_codigo' => 'PED-20240101-0002', 'cliente_nombre' => 'Empresa XYZ'],
        ];

        // When: se busca por código de pedido parcial
        $resultado = $service->buscarVenta($ventas, 'PED-20240101-0001');

        // Then: debe devolver 1 venta
        $this->assertCount(1, $resultado);
        $this->assertSame('VEN-001', $resultado[0]['codigo_venta']);
    }

    public function test_buscar_venta_por_cliente(): void
    {
        // Given: una lista de ventas
        $service = new VentaService();
        $ventas = [
            ['codigo_venta' => 'VEN-001', 'pedido_codigo' => 'PED-001', 'cliente_nombre' => 'Empresa ABC'],
            ['codigo_venta' => 'VEN-002', 'pedido_codigo' => 'PED-002', 'cliente_nombre' => 'Empresa XYZ'],
        ];

        // When: se busca por nombre de cliente
        $resultado = $service->buscarVenta($ventas, 'XYZ');

        // Then: debe devolver 1 venta
        $this->assertCount(1, $resultado);
        $this->assertSame('VEN-002', $resultado[0]['codigo_venta']);
    }

    public function test_buscar_venta_sin_resultados(): void
    {
        // Given: una lista de ventas
        $service = new VentaService();
        $ventas = [
            ['codigo_venta' => 'VEN-001', 'pedido_codigo' => 'PED-001', 'cliente_nombre' => 'Empresa ABC'],
        ];

        // When: se busca algo que no existe
        $resultado = $service->buscarVenta($ventas, 'ZZZ');

        // Then: debe devolver vacío
        $this->assertEmpty($resultado);
    }

    // ========== GenerarCodigoVenta ==========

    public function test_generar_codigo_venta_tiene_formato_correcto(): void
    {
        // Given: un servicio de ventas
        $service = new VentaService();

        // When: se genera un código de venta
        $codigo = $service->generarCodigoVenta();

        // Then: debe empezar con "VEN-" y seguir el formato VEN-YYYYMMDD-XXXX
        $this->assertMatchesRegularExpression('/^VEN-\d{8}-\d{4}$/', $codigo);
    }

    // ========== PRUEBAS CON MOCK ==========

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    public function test_registra_venta_usando_mock_del_repositorio(): void
    {
        // Given: un mock que guarda la venta y le asigna ID
        $mockRepo = \Mockery::mock(\App\Contracts\RepositorioVentaInterface::class);
        $mockRepo->shouldReceive('guardar')
            ->once()
            ->andReturnUsing(function ($venta) {
                $venta['id'] = 50;
                return $venta;
            });

        $service = new VentaService($mockRepo);

        // When: se registra la venta
        $venta = $service->registrarVenta($this->pedido, 2);

        // Then: la venta debe tener ID asignado por el repositorio
        $this->assertSame(50, $venta['id']);
        $this->assertSame(2, $venta['vendedor_id']);
    }

    public function test_consultar_ventas_usa_repositorio_si_existe(): void
    {
        // Given: un mock que devuelve 2 ventas
        $mockRepo = \Mockery::mock(\App\Contracts\RepositorioVentaInterface::class);
        $mockRepo->shouldReceive('obtenerTodas')
            ->once()
            ->andReturn([
                ['codigo_venta' => 'VEN-001', 'total' => 100],
                ['codigo_venta' => 'VEN-002', 'total' => 200],
            ]);

        $service = new VentaService($mockRepo);

        // When: se consultan las ventas
        $resultado = $service->consultarVentas();

        // Then: debe devolver las 2 ventas del repositorio
        $this->assertCount(2, $resultado);
    }
}