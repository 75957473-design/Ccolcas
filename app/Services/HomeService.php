<?php

namespace App\Services;

class HomeService
{
    /**
     * Devuelve la información general de la empresa CCOLCAS.
     */
    public function informacionEmpresa(): array
    {
        return [
            'nombre' => 'CCOLCAS',
            'descripcion' => 'Empresa dedicada a la venta de productos de limpieza',
            'mision' => 'Brindar productos de limpieza de calidad a nuestros clientes',
            'vision' => 'Ser la empresa líder en distribución de productos de limpieza',
        ];
    }

    /**
     * Devuelve las opciones de navegación disponibles en la página principal.
     */
    public function opcionesNavegacion(): array
    {
        return [
            ['nombre' => 'Catálogo', 'ruta' => '/catalogo'],
            ['nombre' => 'Iniciar sesión', 'ruta' => '/login'],
            ['nombre' => 'Registrarse', 'ruta' => '/registro'],
        ];
    }

    /**
     * Busca una opción de navegación por su nombre.
     */
    public function buscarOpcion(string $nombre): ?array
    {
        foreach ($this->opcionesNavegacion() as $opcion) {
            if (strcasecmp($opcion['nombre'], $nombre) === 0) {
                return $opcion;
            }
        }

        return null;
    }
}