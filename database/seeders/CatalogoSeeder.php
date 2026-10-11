<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\StockPorAlmacen;
use Illuminate\Database\Seeder;

class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        // Reutilizar el almacén principal evita crear uno nuevo en cada ejecución.
        $almacen = Almacen::firstOrCreate(
            ['nombre' => 'Almacén principal'],
            ['direccion' => 'Cusco', 'activo' => true]
        );

        // Datos de demostración: cada producto incluye categoría, ficha, imagen y stock inicial.
        $productos = [
            [
                'categoria' => ['nombre' => 'Detergentes', 'slug' => 'detergentes'],
                'producto' => [
                    'codigo' => 'DEMO-DETERGENTE-5L',
                    'nombre' => 'Detergente Líquido Industrial 5 Litros',
                    'descripcion' => 'Detergente líquido para limpieza de ropa y superficies.',
                    'imagen' => 'images/products/detergente-industrial.jpg',
                    'precio_venta' => 35.00,
                    'stock_minimo' => 5,
                ],
                'stock' => 24,
            ],
            [
                'categoria' => ['nombre' => 'Desinfectantes', 'slug' => 'desinfectantes'],
                'producto' => [
                    'codigo' => 'DEMO-LEJIA-001',
                    'nombre' => 'Lejía Concentrada Multi-Superficies',
                    'descripcion' => 'Desinfectante concentrado para superficies lavables.',
                    'imagen' => 'images/products/lejia-multisuperficies.jpg',
                    'precio_venta' => 12.50,
                    'stock_minimo' => 5,
                ],
                'stock' => 30,
            ],
            [
                'categoria' => ['nombre' => 'Higiene Personal', 'slug' => 'higiene-personal'],
                'producto' => [
                    'codigo' => 'DEMO-JABON-001',
                    'nombre' => 'Jabón Líquido Antibacterial para Manos',
                    'descripcion' => 'Jabón antibacterial para higiene y cuidado de manos.',
                    'imagen' => 'images/products/jabon-antibacterial.jpg',
                    'precio_venta' => 18.00,
                    'stock_minimo' => 5,
                ],
                'stock' => 18,
            ],
        ];

        // Crear o completar cada categoría y producto sin duplicarlos por sus claves únicas.
        foreach ($productos as $datos) {
            $categoria = Categoria::firstOrCreate(
                ['slug' => $datos['categoria']['slug']],
                [
                    'nombre' => $datos['categoria']['nombre'],
                    'activo' => true,
                ]
            );

            // Mantener visibles en el catálogo las categorías incluidas en este seeder.
            if (!$categoria->activo) {
                $categoria->update(['activo' => true]);
            }

            $producto = Producto::firstOrCreate(
                ['codigo' => $datos['producto']['codigo']],
                array_merge($datos['producto'], [
                    'categoria_id' => $categoria->id,
                    'activo' => true,
                ])
            );

            // Reactivar el producto demo si se había deshabilitado.
            if (!$producto->activo) {
                $producto->update(['activo' => true]);
            }

            // Completar la imagen solo si falta; así se conserva cualquier imagen personalizada.
            if (!$producto->imagen) {
                $producto->update(['imagen' => $datos['producto']['imagen']]);
            }

            // Crear el stock inicial una sola vez y conservar la cantidad existente al reejecutar.
            StockPorAlmacen::firstOrCreate(
                [
                    'producto_id' => $producto->id,
                    'almacen_id' => $almacen->id,
                ],
                ['cantidad' => $datos['stock']]
            );
        }
    }
}