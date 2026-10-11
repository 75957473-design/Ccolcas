<?php

namespace Tests\Unit;

use App\Services\ConfirmacionPedidoService;
use PHPUnit\Framework\TestCase;

class ConfirmacionPedidoServiceTest extends TestCase
{
    private array $pedidoRegistrado;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pedidoRegistrado = [
            'codigo'     => 'PED-20240101-0001',
            'estado'     => 'pendiente',
            'cliente_id' => 1,
            'items'      => [
                ['nombre' => 'Detergente', 'precio' => 15.50, 'cantidad' => 2, 'subtotal' => 31.00],
                ['nombre' => 'Lejía', 'precio' => 5.00, 'cantidad' => 3, 'subtotal' => 15.00],
            ],
        ];
    }

    // ========== ConfirmarPedido ==========

    public function test_confirmar_pedido_registrado(): void
    {
        // Given: un pedido registrado correctamente
        $service = new ConfirmacionPedidoService();

        // When: se confirma el pedido
        $confirmacion = $service->confirmarPedido($this->pedidoRegistrado);

        // Then: debe indicar confirmado con el código del pedido
        $this->assertTrue($confirmacion['confirmado']);
        $this->assertSame('PED-20240101-0001', $confirmacion['codigo']);
        $this->assertArrayHasKey('mensaje', $confirmacion);
    }

    public function test_confirmar_pedido_no_registrado_lanza_excepcion(): void
    {
        // Given: un pedido sin código (no registrado)
        $service = new ConfirmacionPedidoService();
        $pedidoSinCodigo = ['estado' => 'pendiente', 'items' => []];

        // Then: se espera una excepción
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El pedido no ha sido registrado correctamente.');

        // When: se intenta confirmar el pedido
        $service->confirmarPedido($pedidoSinCodigo);
    }

    // ========== ValidarConfirmacion ==========

    public function test_validar_pedido_registrado_retorna_true(): void
    {
        // Given: un servicio de confirmación
        $service = new ConfirmacionPedidoService();

        // When: se valida un pedido con código y estado
        $resultado = $service->validarConfirmacion($this->pedidoRegistrado);

        // Then: debe devolver true
        $this->assertTrue($resultado);
    }

    public function test_validar_pedido_sin_codigo_retorna_false(): void
    {
        // Given: un pedido sin código
        $service = new ConfirmacionPedidoService();

        // When: se valida el pedido
        $resultado = $service->validarConfirmacion(['estado' => 'pendiente']);

        // Then: debe devolver false
        $this->assertFalse($resultado);
    }

    public function test_validar_pedido_sin_estado_retorna_false(): void
    {
        // Given: un pedido sin estado
        $service = new ConfirmacionPedidoService();

        // When: se valida el pedido
        $resultado = $service->validarConfirmacion(['codigo' => 'PED-001']);

        // Then: debe devolver false
        $this->assertFalse($resultado);
    }

    // ========== MostrarCodigoPedido ==========

    public function test_mostrar_codigo_pedido(): void
    {
        // Given: un pedido con código
        $service = new ConfirmacionPedidoService();

        // When: se solicita el código del pedido
        $codigo = $service->mostrarCodigoPedido($this->pedidoRegistrado);

        // Then: debe devolver el código
        $this->assertSame('PED-20240101-0001', $codigo);
    }

    public function test_mostrar_codigo_de_pedido_sin_codigo_lanza_excepcion(): void
    {
        // Given: un pedido sin código
        $service = new ConfirmacionPedidoService();

        // Then: se espera una excepción
        $this->expectException(\InvalidArgumentException::class);

        // When: se solicita el código
        $service->mostrarCodigoPedido(['estado' => 'pendiente']);
    }

    // ========== MostrarDetallePedido ==========

    public function test_mostrar_detalle_pedido_con_productos(): void
    {
        // Given: un pedido con productos
        $service = new ConfirmacionPedidoService();

        // When: se solicita el detalle del pedido
        $detalle = $service->mostrarDetallePedido($this->pedidoRegistrado);

        // Then: debe mostrar nombre, cantidad y subtotal
        $this->assertCount(2, $detalle);
        $this->assertSame('Detergente', $detalle[0]['nombre']);
        $this->assertSame(2, $detalle[0]['cantidad']);
        $this->assertSame(31.00, $detalle[0]['subtotal']);
    }

    // ========== ConsultarPedido ==========

    public function test_consultar_pedido_por_codigo(): void
    {
        // Given: una lista con pedidos registrados
        $service = new ConfirmacionPedidoService();
        $pedidos = [
            ['codigo' => 'PED-20240101-0001', 'estado' => 'pendiente'],
            ['codigo' => 'PED-20240101-0002', 'estado' => 'enviado'],
        ];

        // When: se consulta por el código del segundo
        $pedido = $service->consultarPedido($pedidos, 'PED-20240101-0002');

        // Then: debe devolver el pedido correcto
        $this->assertNotNull($pedido);
        $this->assertSame('enviado', $pedido['estado']);
    }

    public function test_consultar_pedido_inexistente_devuelve_null(): void
    {
        // Given: una lista con pedidos
        $service = new ConfirmacionPedidoService();
        $pedidos = [
            ['codigo' => 'PED-20240101-0001', 'estado' => 'pendiente'],
        ];

        // When: se consulta un código que no existe
        $pedido = $service->consultarPedido($pedidos, 'PED-9999-0001');

        // Then: debe devolver null
        $this->assertNull($pedido);
    }

    // ========== Mensaje ==========

    public function test_mensaje_confirmacion(): void
    {
        // Given: un servicio de confirmación
        $service = new ConfirmacionPedidoService();

        // When: se solicita el mensaje de confirmación
        $mensaje = $service->mensajeConfirmacion();

        // Then: debe devolver el mensaje esperado
        $this->assertSame('Su pedido ha sido registrado correctamente.', $mensaje);
    }
}