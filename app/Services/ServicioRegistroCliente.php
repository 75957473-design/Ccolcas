<?php

namespace App\Services;

use InvalidArgumentException;

class ServicioRegistroCliente
{
	private const CAMPOS_OBLIGATORIOS = [
		'nombre',
		'documento',
		'telefono',
		'direccion',
		'correo',
		'contrasena',
	];

	public function mostrarRegistro(): array
	{
		return [
			'estado' => true,
			'vista' => 'Formulario de Registro de Cliente',
		];
	}

	public function validarCampos(array $datos): void
	{
		foreach (self::CAMPOS_OBLIGATORIOS as $campo) {
			if (!isset($datos[$campo]) || !is_scalar($datos[$campo]) || trim((string) $datos[$campo]) === '') {
				throw new InvalidArgumentException("El campo {$campo} es obligatorio.");
			}
		}
	}

	public function validarCorreo(string $correo): bool
	{
		return filter_var(trim($correo), FILTER_VALIDATE_EMAIL) !== false;
	}

	public function registrarContrasena(string $contrasena): bool
	{
		return strlen($contrasena) >= 6;
	}

	public function validarCliente(string $documento, string $correo, array $clientes): void
	{
		foreach ($clientes as $cliente) {
			$documentoRegistrado = (string) ($cliente['documento'] ?? '');
			$correoRegistrado = (string) ($cliente['correo'] ?? '');

			if (trim($documentoRegistrado) === trim($documento)
				|| strtolower(trim($correoRegistrado)) === strtolower(trim($correo))) {
				throw new InvalidArgumentException('El cliente ya se encuentra registrado.');
			}
		}
	}

	public function registrarCliente(array $datos, array &$clientes): array
	{
		$this->validarCampos($datos);

		if (!$this->validarCorreo((string) $datos['correo'])) {
			throw new InvalidArgumentException('El correo electrónico no tiene un formato válido.');
		}

		if (!$this->registrarContrasena((string) $datos['contrasena'])) {
			throw new InvalidArgumentException('La contraseña debe tener al menos 6 caracteres.');
		}

		$this->validarCliente((string) $datos['documento'], (string) $datos['correo'], $clientes);

		$cliente = $datos;
		$cliente['contrasena'] = password_hash((string) $datos['contrasena'], PASSWORD_DEFAULT);
		$clientes[] = $cliente;

		return [
			'registrado' => true,
			'mensaje' => 'El cliente fue registrado correctamente.',
			'cliente' => $cliente,
		];
	}

	public function confirmarRegistro(): array
	{
		return [
			'estado' => true,
			'mensaje' => 'El cliente fue registrado correctamente.',
		];
	}
}

