<?php

namespace App\Services;

use App\Contracts\RepositorioUsuarioInterface;

class AutenticacionService
{
    public function __construct(
        private ?RepositorioUsuarioInterface $usuarios = null
    ) {
    }

    /**
     * Valida que los campos de usuario y contraseña no estén vacíos.
     */
    public function validarCampos(string $usuario, string $password): void
    {
        if (trim($usuario) === '') {
            throw new \InvalidArgumentException('El campo usuario es obligatorio.');
        }

        if (trim($password) === '') {
            throw new \InvalidArgumentException('El campo contraseña es obligatorio.');
        }
    }

    /**
     * Devuelve el nombre legible del rol.
     */
    public function identificarRol(string $rol): string
    {
        return match (strtolower($rol)) {
            'admin'    => 'Administrador',
            'vendedor' => 'Vendedor',
            'almacen'  => 'Encargado de almacén',
            'cliente'  => 'Cliente',
            default    => 'Rol desconocido',
        };
    }

    /**
     * Devuelve el mensaje de error cuando las credenciales son incorrectas.
     */
    public function mensajeErrorCredenciales(): string
    {
        return 'Las credenciales ingresadas no son válidas.';
    }

    /**
     * Determina si un rol tiene acceso al sistema.
     */
    public function rolPermitido(string $rol): bool
    {
        $rolesValidos = ['admin', 'vendedor', 'almacen', 'cliente'];
        return in_array(strtolower($rol), $rolesValidos, true);
    }

    /**
     * Valida al usuario usando el repositorio.
     * Lanza excepción si las credenciales son incorrectas.
     */
    public function validarUsuario(string $correo, string $password): array
    {
        $this->validarCampos($correo, $password);

        if ($this->usuarios === null) {
            throw new \RuntimeException('No hay repositorio de usuarios configurado.');
        }

        if (! $this->usuarios->verificarCredenciales($correo, $password)) {
            throw new \InvalidArgumentException($this->mensajeErrorCredenciales());
        }

        $usuario = $this->usuarios->buscarPorCorreo($correo);

        return [
            'autenticado' => true,
            'usuario'     => $usuario,
            'rol'         => $this->identificarRol($usuario['rol'] ?? 'cliente'),
        ];
    }
}