<?php

namespace Tests\Unit;

use App\Services\AutenticacionService;
use PHPUnit\Framework\TestCase;

class AutenticacionServiceTest extends TestCase
{
    public function test_usuario_y_password_validos_no_lanzan_excepcion(): void
    {
        // Given: un servicio de autenticación
        $service = new AutenticacionService();

        // When: se validan campos con valores no vacíos
        $service->validarCampos('juan@colcas.com', 'miClave123');

        // Then: no se lanza ninguna excepción (si llegamos aquí, pasó)
        $this->assertTrue(true);
    }

    public function test_usuario_vacio_lanza_excepcion(): void
    {
        // Given: un servicio de autenticación
        $service = new AutenticacionService();

        // Then: se espera una excepción
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El campo usuario es obligatorio.');

        // When: se intenta validar con usuario vacío
        $service->validarCampos('', 'miClave123');
    }

    public function test_password_vacio_lanza_excepcion(): void
    {
        // Given: un servicio de autenticación
        $service = new AutenticacionService();

        // Then: se espera una excepción
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El campo contraseña es obligatorio.');

        // When: se intenta validar con contraseña vacía
        $service->validarCampos('juan@colcas.com', '');
    }

    public function test_identifica_rol_administrador(): void
    {
        // Given: un servicio de autenticación
        $service = new AutenticacionService();

        // When: se identifica el rol "admin"
        $rol = $service->identificarRol('admin');

        // Then: debe devolver "Administrador"
        $this->assertSame('Administrador', $rol);
    }

    public function test_identifica_rol_vendedor(): void
    {
        // Given: un servicio de autenticación
        $service = new AutenticacionService();

        // When: se identifica el rol "vendedor"
        $rol = $service->identificarRol('vendedor');

        // Then: debe devolver "Vendedor"
        $this->assertSame('Vendedor', $rol);
    }

    public function test_identifica_rol_cliente(): void
    {
        // Given: un servicio de autenticación
        $service = new AutenticacionService();

        // When: se identifica el rol "cliente"
        $rol = $service->identificarRol('cliente');

        // Then: debe devolver "Cliente"
        $this->assertSame('Cliente', $rol);
    }

    public function test_identifica_rol_desconocido(): void
    {
        // Given: un servicio de autenticación
        $service = new AutenticacionService();

        // When: se identifica un rol que no existe
        $rol = $service->identificarRol('superadmin');

        // Then: debe devolver "Rol desconocido"
        $this->assertSame('Rol desconocido', $rol);
    }

    public function test_mensaje_de_error_de_credenciales(): void
    {
        // Given: un servicio de autenticación
        $service = new AutenticacionService();

        // When: se solicita el mensaje de error de credenciales
        $mensaje = $service->mensajeErrorCredenciales();

        // Then: debe devolver el mensaje esperado
        $this->assertSame('Las credenciales ingresadas no son válidas.', $mensaje);
    }

    public function test_rol_permitido_admin(): void
    {
        // Given: un servicio de autenticación
        $service = new AutenticacionService();

        // When: se verifica si "admin" tiene acceso
        $resultado = $service->rolPermitido('admin');

        // Then: debe devolver true
        $this->assertTrue($resultado);
    }

    public function test_rol_no_permitido(): void
    {
        // Given: un servicio de autenticación
        $service = new AutenticacionService();

        // When: se verifica si "invitado" tiene acceso
        $resultado = $service->rolPermitido('invitado');

        // Then: debe devolver false
        $this->assertFalse($resultado);
    }


    // ========== PRUEBAS CON MOCK ==========

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    public function test_valida_usuario_correctamente_con_mock(): void
    {
        // Given: un mock del repositorio que acepta las credenciales
        $mockRepo = \Mockery::mock(\App\Contracts\RepositorioUsuarioInterface::class);
        $mockRepo->shouldReceive('verificarCredenciales')
            ->once()
            ->with('juan@colcas.com', 'clave123')
            ->andReturn(true);

        $mockRepo->shouldReceive('buscarPorCorreo')
            ->once()
            ->with('juan@colcas.com')
            ->andReturn([
                'id' => 1,
                'correo' => 'juan@colcas.com',
                'rol' => 'admin',
            ]);

        $service = new \App\Services\AutenticacionService($mockRepo);

        // When: se valida al usuario
        $resultado = $service->validarUsuario('juan@colcas.com', 'clave123');

        // Then: debe estar autenticado con rol Administrador
        $this->assertTrue($resultado['autenticado']);
        $this->assertSame('Administrador', $resultado['rol']);
    }

    public function test_rechaza_credenciales_invalidas_con_mock(): void
    {
        // Given: un mock que rechaza las credenciales
        $mockRepo = \Mockery::mock(\App\Contracts\RepositorioUsuarioInterface::class);
        $mockRepo->shouldReceive('verificarCredenciales')
            ->once()
            ->with('juan@colcas.com', 'claveIncorrecta')
            ->andReturn(false);

        $service = new \App\Services\AutenticacionService($mockRepo);

        // Then: se espera una excepción
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Las credenciales ingresadas no son válidas.');

        // When: se intenta validar al usuario
        $service->validarUsuario('juan@colcas.com', 'claveIncorrecta');
    }

    public function test_no_permite_validar_sin_repositorio(): void
    {
        // Given: un servicio sin repositorio inyectado
        $service = new \App\Services\AutenticacionService();

        // Then: se espera una excepción
        $this->expectException(\RuntimeException::class);

        // When: se intenta validar
        $service->validarUsuario('juan@colcas.com', 'clave123');
    }


}