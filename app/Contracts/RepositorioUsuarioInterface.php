<?php

namespace App\Contracts;

interface RepositorioUsuarioInterface
{
    /**
     * Busca un usuario por su correo.
     * Devuelve null si no existe.
     */
    public function buscarPorCorreo(string $correo): ?array;

    /**
     * Verifica si las credenciales son válidas.
     */
    public function verificarCredenciales(string $correo, string $password): bool;
}