<?php

namespace Tests\Unit;

use App\Services\ServicioRegistroCliente;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ServicioRegistroClienteTest extends TestCase
{
    private function clienteExistente(): array
    {
        return [
            [
                'nombre' => 'Juan Pérez',
                'documento' => '71234567',
                'telefono' => '987654321',
                'direccion' => 'Av. El Sol 123',
                'correo' => 'juan@ccolcas.com',
                'contrasena' => '123456',
            ],
        ];
    }

    private function datosClienteNuevo(): array
    {
        return [
            'nombre' => 'Carlos Ramos',
            'documento' => '20601234567',
            'telefono' => '955443322',
            'direccion' => 'Av. Cultura 789',
            'correo' => 'carlos@ccolcas.com',
            'contrasena' => 'clave123',
        ];
    }

    public function test_muestra_formulario_de_registro(): void
    {
        // Given: Se crea el servicio para simular la solicitud de registro de un visitante.
        $servicio = new ServicioRegistroCliente();

        // When: Se solicita mostrar el formulario de registro de cliente.
        $resultado = $servicio->mostrarRegistro();

        // Then: Se verifica que el formulario se muestre correctamente.
        $this->assertTrue($resultado['estado']);
        $this->assertSame('Formulario de Registro de Cliente', $resultado['vista']);
    }

    public function test_informa_error_si_faltan_campos_obligatorios(): void
    {
        // Given: Se preparan los datos de un cliente nuevo dejando vacío un campo obligatorio.
        $servicio = new ServicioRegistroCliente();
        $datos = $this->datosClienteNuevo();
        $datos['documento'] = '';

        // Then: Se espera que el sistema informe que los datos ingresados no son válidos.
        $this->expectException(InvalidArgumentException::class);

        // When: Se validan los campos incompletos del nuevo cliente.
        $servicio->validarCampos($datos);
    }

    public function test_acepta_correo_con_formato_valido(): void
    {
        // Given: Se prepara un correo electrónico que cumple con el formato esperado.
        $servicio = new ServicioRegistroCliente();

        // When: Se valida el formato del correo electrónico ingresado.
        $resultado = $servicio->validarCorreo('cliente@ccolcas.com');

        // Then: Se comprueba que el correo sea aceptado como válido.
        $this->assertTrue($resultado);
    }

    public function test_rechaza_correo_con_formato_invalido(): void
    {
        // Given: Se prepara un correo electrónico con un formato incorrecto.
        $servicio = new ServicioRegistroCliente();

        // When: Se valida el formato del correo electrónico incorrecto.
        $resultado = $servicio->validarCorreo('correo-invalido');

        // Then: Se comprueba que el sistema rechace el correo como inválido.
        $this->assertFalse($resultado);
    }

    public function test_acepta_contrasena_valida(): void
    {
        // Given: Se prepara una contraseña que cumple con las condiciones requeridas.
        $servicio = new ServicioRegistroCliente();

        // When: Se valida la contraseña ingresada por el cliente.
        $resultado = $servicio->registrarContrasena('clave123');

        // Then: Se comprueba que la contraseña sea aceptada correctamente.
        $this->assertTrue($resultado);
    }

    public function test_informa_si_documento_ya_esta_registrado(): void
    {
        // Given: Se prepara una lista que contiene un cliente con un documento ya registrado.
        $servicio = new ServicioRegistroCliente();
        $clientes = $this->clienteExistente();

        // Then: Se espera que el sistema informe que el documento ya está registrado.
        $this->expectException(InvalidArgumentException::class);

        // When: Se intenta validar un cliente utilizando un documento existente.
        $servicio->validarCliente('71234567', 'nuevo@ccolcas.com', $clientes);
    }

    public function test_informa_si_correo_ya_esta_registrado(): void
    {
        // Given: Se prepara una lista que contiene un cliente con un correo ya registrado.
        $servicio = new ServicioRegistroCliente();
        $clientes = $this->clienteExistente();

        // ThenSe espera que el sistema informe que el correo ya está registrado.
        $this->expectException(InvalidArgumentException::class);

        // When: Se intenta validar un cliente utilizando un correo existente.
        $servicio->validarCliente('12345678', 'juan@ccolcas.com', $clientes);
    }

    public function test_registra_cliente_con_datos_validos(): void
    {
        // Given: Se prepara un cliente nuevo con todos sus datos válidos y un cliente existente.
        $servicio = new ServicioRegistroCliente();
        $clientes = $this->clienteExistente();
        $datos = $this->datosClienteNuevo();

        // When: Se registra el nuevo cliente utilizando los datos preparados.
        $resultado = $servicio->registrarCliente($datos, $clientes);

        // Then: Se comprueba que el cliente haya sido registrado y agregado a la lista.
        $this->assertTrue($resultado['registrado']);
        $this->assertCount(2, $clientes);
        $this->assertSame('20601234567', $clientes[1]['documento']);
        $this->assertSame('carlos@ccolcas.com', $clientes[1]['correo']);
    }

    public function test_devuelve_confirmacion_de_registro_exitoso(): void
    {
        // Given: Se crea el servicio para confirmar que el registro del cliente fue realizado.
        $servicio = new ServicioRegistroCliente();

        // When:Se solicita la confirmación del registro realizado.
        $resultado = $servicio->confirmarRegistro();

        // Then: Se comprueba que el sistema devuelva el mensaje de registro exitoso.
        $this->assertTrue($resultado['estado']);
        $this->assertSame('El cliente fue registrado correctamente.', $resultado['mensaje']);
    }
}


