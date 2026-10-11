<?php

namespace App\Services;

use App\Contracts\ServicioCorreoInterface;

class RegistroClienteService
{
    public function __construct(
        private ?ServicioCorreoInterface $correo = null
    ) {
    }

    /**
     * Valida que los campos obligatorios del cliente no estén vacíos.
     */
    public function validarCampos(array $datos): void
    {
        $obligatorios = ['nombre', 'documento', 'telefono', 'direccion', 'correo', 'password'];

        foreach ($obligatorios as $campo) {
            if (! isset($datos[$campo]) || trim((string) $datos[$campo]) === '') {
                throw new \InvalidArgumentException("El campo {$campo} es obligatorio.");
            }
        }
    }

    /**
     * Valida que el correo tenga un formato válido.
     */
    public function validarCorreo(string $correo): bool
    {
        return filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida que el documento sea un DNI (8 dígitos) o RUC (11 dígitos).
     */
    public function validarDocumento(string $documento): bool
    {
        $documento = trim($documento);

        if (strlen($documento) === 8 && ctype_digit($documento)) {
            return true;
        }

        if (strlen($documento) === 11 && ctype_digit($documento)) {
            return true;
        }

        return false;
    }

    /**
     * Valida que la contraseña tenga al menos 8 caracteres.
     */
    public function validarPassword(string $password): bool
    {
        return strlen($password) >= 8;
    }

    /**
     * Verifica si un cliente ya está registrado (por documento o correo).
     */
    public function clienteYaRegistrado(array $clientesExistentes, string $documento, string $correo): bool
    {
        foreach ($clientesExistentes as $cliente) {
            if ($cliente['documento'] === $documento || $cliente['correo'] === $correo) {
                return true;
            }
        }

        return false;
    }

    /**
     * Devuelve el mensaje de confirmación del registro.
     */
    public function mensajeConfirmacion(): string
    {
        return 'Cliente registrado correctamente.';
    }

    /**
     * Registra un cliente: valida todo, agrega a la lista y envía correo si hay servicio.
     */
    public function registrarCliente(array $datos, array &$clientesExistentes): array
    {
        $this->validarCampos($datos);

        if (! $this->validarCorreo($datos['correo'])) {
            throw new \InvalidArgumentException('El correo no tiene un formato válido.');
        }

        if (! $this->validarDocumento($datos['documento'])) {
            throw new \InvalidArgumentException('El documento debe ser un DNI o RUC válido.');
        }

        if ($this->clienteYaRegistrado($clientesExistentes, $datos['documento'], $datos['correo'])) {
            throw new \InvalidArgumentException('El cliente ya se encuentra registrado.');
        }

        $clientesExistentes[] = $datos;

        if ($this->correo !== null) {
            $this->correo->enviarCorreo(
                $datos['correo'],
                "¡Bienvenido {$datos['nombre']}!"
            );
        }

        return [
            'registrado' => true,
            'mensaje'    => 'El cliente fue registrado correctamente.',
        ];
    }
}