<?php

namespace App\Services;

use App\Contracts\RepositorioProductoInterface;

class CatalogoService
{
    public function __construct(
        private ?RepositorioProductoInterface $repositorio = null
    ) {
    }

    /**
     * Filtra los productos activos (disponibles para comercialización).
     */
    public function productosDisponibles(array $productos): array
    {
        return array_values(array_filter($productos, function ($producto) {
            return ($producto['activo'] ?? false) === true;
        }));
    }

    /**
     * Busca productos por nombre (coincidencia parcial, case-insensitive).
     */
    public function buscarPorNombre(array $productos, string $nombre): array
    {
        $nombre = strtolower(trim($nombre));

        return array_values(array_filter($productos, function ($producto) use ($nombre) {
            return str_contains(strtolower($producto['nombre']), $nombre);
        }));
    }

    /**
     * Busca productos por categoría (coincidencia exacta, case-insensitive).
     */
    public function buscarPorCategoria(array $productos, string $categoria): array
    {
        $categoria = strtolower(trim($categoria));

        return array_values(array_filter($productos, function ($producto) use ($categoria) {
            return strtolower($producto['categoria']) === $categoria;
        }));
    }

    /**
     * Verifica si un producto tiene stock disponible.
     */
    public function tieneStock(array $producto): bool
    {
        return ($producto['stock'] ?? 0) > 0;
    }

    /**
     * Devuelve la información resumida de un producto.
     */
    public function informacionProducto(array $producto): array
    {
        return [
            'nombre'          => $producto['nombre'],
            'caracteristicas' => $producto['caracteristicas'] ?? '',
            'precio'          => $producto['precio'],
            'stock'           => $producto['stock'],
            'disponible'      => $this->tieneStock($producto),
        ];
    }

    /**
     * Devuelve un producto por su ID, o null si no existe.
     */
    public function consultarPorId(array $productos, int $id): ?array
    {
        foreach ($productos as $producto) {
            if ($producto['id'] === $id) {
                return $producto;
            }
        }

        return null;
    }

    /**
     * Obtiene el catálogo usando el repositorio (mock o real).
     */
    public function obtenerCatalogo(): array
    {
        if ($this->repositorio === null) {
            throw new \RuntimeException('No hay repositorio de productos configurado.');
        }

        return $this->repositorio->obtenerProductosActivos();
    }

    /**
     * Consulta un producto usando el repositorio (mock o real).
     */
    public function consultarProducto(int $id): ?array
    {
        if ($this->repositorio === null) {
            throw new \RuntimeException('No hay repositorio de productos configurado.');
        }

        return $this->repositorio->buscarPorId($id);
    }
}