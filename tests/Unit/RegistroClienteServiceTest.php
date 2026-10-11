<?php

namespace Tests\Unit;

use App\Services\RegistroClienteService;
use PHPUnit\Framework\TestCase;

class RegistroClienteServiceTest extends TestCase
{
    // ========== ValidarCampos ==========

    public function test_datos_validos_no_lanzan_excepcion(): void
    {
        // Given: un cliente con todos los datos obligatorios
        $service = new RegistroClienteService();
        $datos = [
            'nombre'    => 'Empresa ABC',
            'documento' => '20123456789',
            'telefono'  => '999888777',
            'direccion' => 'Av. Lima 123',
            'correo'    => 'contacto@abc.com',
            'password'  => 'miClave123',
        ];

        // When: se validan los campos
        $service->validarCampos($datos);

        // Then: no se lanza excepción
        $this->assertTrue(true);
    }

    public function test_nombre_vacio_lanza_excepcion(): void
    {
        // Given: un cliente sin nombre
        $service = new RegistroClienteService();
        $datos = [
            'nombre'    => '',
            'documento' => '20123456789',
            'telefono'  => '999888777',
            'direccion' => 'Av. Lima 123',
            'correo'    => 'contacto@abc.com',
            'password'  => 'miClave123',
        ];

        // Then: se espera una excepción
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El campo nombre es obligatorio.');

        // When: se validan los campos
        $service->validarCampos($datos);
    }

    public function test_datos_incompletos_lanzan_excepcion(): void
    {
        // Given: un cliente sin correo
        $service = new RegistroClienteService();
        $datos = [
            'nombre'    => 'Empresa ABC',
            'documento' => '20123456789',
            'telefono'  => '999888777',
            'direccion' => 'Av. Lima 123',
            'correo'    => '',
            'password'  => 'miClave123',
        ];

        // Then: se espera una excepción
        $this->expectException(\InvalidArgumentException::class);

        // When: se validan los campos
        $service->validarCampos($datos);
    }

    // ========== ValidarCorreo ==========

    public function test_correo_valido_retorna_true(): void
    {
        // Given: un servicio de registro
        $service = new RegistroClienteService();

        // When: se valida un correo con formato válido
        $resultado = $service->validarCorreo('cliente@colcas.com');

        // Then: debe devolver true
        $this->assertTrue($resultado);
    }

    public function test_correo_invalido_retorna_false(): void
    {
        // Given: un servicio de registro
        $service = new RegistroClienteService();

        // When: se valida un correo con formato inválido
        $resultado = $service->validarCorreo('correo-invalido');

        // Then: debe devolver false
        $this->assertFalse($resultado);
    }

    // ========== ValidarDocumento ==========

    public function test_dni_valido_retorna_true(): void
    {
        // Given: un servicio de registro
        $service = new RegistroClienteService();

        // When: se valida un DNI de 8 dígitos
        $resultado = $service->validarDocumento('12345678');

        // Then: debe devolver true
        $this->assertTrue($resultado);
    }

    public function test_ruc_valido_retorna_true(): void
    {
        // Given: un servicio de registro
        $service = new RegistroClienteService();

        // When: se valida un RUC de 11 dígitos
        $resultado = $service->validarDocumento('20123456789');

        // Then: debe devolver true
        $this->assertTrue($resultado);
    }

    public function test_documento_invalido_retorna_false(): void
    {
        // Given: un servicio de registro
        $service = new RegistroClienteService();

        // When: se valida un documento con letras o longitud incorrecta
        $resultado = $service->validarDocumento('ABC123');

        // Then: debe devolver false
        $this->assertFalse($resultado);
    }

    // ========== ValidarPassword ==========

    public function test_password_valida_retorna_true(): void
    {
        // Given: un servicio de registro
        $service = new RegistroClienteService();

        // When: se valida una contraseña de 8+ caracteres
        $resultado = $service->validarPassword('miClave123');

        // Then: debe devolver true
        $this->assertTrue($resultado);
    }

    public function test_password_corta_retorna_false(): void
    {
        // Given: un servicio de registro
        $service = new RegistroClienteService();

        // When: se valida una contraseña de menos de 8 caracteres
        $resultado = $service->validarPassword('123');

        // Then: debe devolver false
        $this->assertFalse($resultado);
    }

    // ========== ClienteYaRegistrado ==========

    public function test_cliente_ya_registrado_por_documento(): void
    {
        // Given: una lista con un cliente ya registrado
        $service = new RegistroClienteService();
        $clientesExistentes = [
            ['documento' => '20123456789', 'correo' => 'contacto@abc.com'],
        ];

        // When: se verifica si un cliente con el mismo documento ya existe
        $resultado = $service->clienteYaRegistrado($clientesExistentes, '20123456789', 'nuevo@abc.com');

        // Then: debe devolver true
        $this->assertTrue($resultado);
    }

    public function test_cliente_ya_registrado_por_correo(): void
    {
        // Given: una lista con un cliente ya registrado
        $service = new RegistroClienteService();
        $clientesExistentes = [
            ['documento' => '20123456789', 'correo' => 'contacto@abc.com'],
        ];

        // When: se verifica si un cliente con el mismo correo ya existe
        $resultado = $service->clienteYaRegistrado($clientesExistentes, '99999999', 'contacto@abc.com');

        // Then: debe devolver true
        $this->assertTrue($resultado);
    }

    public function test_cliente_nuevo_no_esta_registrado(): void
    {
        // Given: una lista con un cliente ya registrado
        $service = new RegistroClienteService();
        $clientesExistentes = [
            ['documento' => '20123456789', 'correo' => 'contacto@abc.com'],
        ];

        // When: se verifica un cliente nuevo
        $resultado = $service->clienteYaRegistrado($clientesExistentes, '88888888', 'nuevo@abc.com');

        // Then: debe devolver false
        $this->assertFalse($resultado);
    }

    // ========== Mensaje de confirmación ==========

    public function test_mensaje_confirmacion_registro(): void
    {
        // Given: un servicio de registro
        $service = new RegistroClienteService();

        // When: se solicita el mensaje de confirmación
        $mensaje = $service->mensajeConfirmacion();

        // Then: debe devolver el mensaje esperado
        $this->assertSame('Cliente registrado correctamente.', $mensaje);
    }

    // ========== PRUEBAS CON MOCK ==========

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    public function test_registra_cliente_y_envia_correo_con_mock(): void
    {
        // Given: un mock del servicio de correo que espera ser llamado una vez
        $correoMock = \Mockery::mock(\App\Contracts\ServicioCorreoInterface::class);
        $correoMock->shouldReceive('enviarCorreo')
            ->once()
            ->with('carlos@colcas.com', '¡Bienvenido Carlos Ramos!')
            ->andReturn(true);

        $service = new \App\Services\RegistroClienteService($correoMock);

        $clientesExistentes = [
            [
                'nombre'    => 'Juan Pérez',
                'documento' => '71234567',
                'telefono'  => '987654321',
                'direccion' => 'Av. El Sol 123',
                'correo'    => 'juan@colcas.com',
                'password'  => '12345678',
            ],
        ];

        $nuevoCliente = [
            'nombre'    => 'Carlos Ramos',
            'documento' => '20601234567',
            'telefono'  => '955443322',
            'direccion' => 'Av. Cultura 789',
            'correo'    => 'carlos@colcas.com',
            'password'  => '65432178',
        ];

        // When: se registra el cliente
        $resultado = $service->registrarCliente($nuevoCliente, $clientesExistentes);

        // Then: debe estar registrado y el cliente agregado
        $this->assertTrue($resultado['registrado']);
        $this->assertCount(2, $clientesExistentes);
    }

    public function test_registra_cliente_sin_enviar_correo_si_no_hay_servicio(): void
    {
        // Given: un servicio sin mock de correo
        $service = new \App\Services\RegistroClienteService();

        $clientesExistentes = [];

        $nuevoCliente = [
            'nombre'    => 'Ana Torres',
            'documento' => '71234567',
            'telefono'  => '955443322',
            'direccion' => 'Av. Lima 456',
            'correo'    => 'ana@colcas.com',
            'password'  => '65432178',
        ];

        // When: se registra el cliente
        $resultado = $service->registrarCliente($nuevoCliente, $clientesExistentes);

        // Then: debe registrarse correctamente sin fallar por falta de correo
        $this->assertTrue($resultado['registrado']);
        $this->assertCount(1, $clientesExistentes);
    }

    public function test_no_registra_cliente_duplicado_con_mock(): void
    {
        // Given: un mock que NO debe ser llamado (porque el registro fallará antes)
        $correoMock = \Mockery::mock(\App\Contracts\ServicioCorreoInterface::class);
        $correoMock->shouldNotReceive('enviarCorreo');

        $service = new \App\Services\RegistroClienteService($correoMock);

        $clientesExistentes = [
            [
                'nombre'    => 'Juan Pérez',
                'documento' => '71234567',
                'telefono'  => '987654321',
                'direccion' => 'Av. El Sol 123',
                'correo'    => 'juan@colcas.com',
                'password'  => '12345678',
            ],
        ];

        // Cliente con el mismo documento
        $clienteDuplicado = [
            'nombre'    => 'Otro Juan',
            'documento' => '71234567', // mismo documento
            'telefono'  => '999999999',
            'direccion' => 'Av. Otra 789',
            'correo'    => 'otro@colcas.com',
            'password'  => '65432178',
        ];

        // Then: se espera una excepción
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El cliente ya se encuentra registrado.');

        // When: se intenta registrar
        $service->registrarCliente($clienteDuplicado, $clientesExistentes);
    }




}