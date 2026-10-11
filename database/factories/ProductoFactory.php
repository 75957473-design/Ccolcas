<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\UnidadMedida;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition(): array
    {
        $costoCompra = $this->faker->randomFloat(2, 5, 50);
        $costoFlete = $this->faker->randomFloat(2, 1, 10);
        $margen = $this->faker->randomFloat(2, 10, 50);
        $precioVenta = round(($costoCompra + $costoFlete) * (1 + $margen / 100), 2);

        return [
            'codigo' => 'PROD-' . $this->faker->unique()->numerify('#####'),
            'nombre' => $this->faker->unique()->words(3, true),
            'descripcion' => $this->faker->sentence(),
            'imagen' => null,
            'categoria_id' => Categoria::factory(),
            'unidad_medida_id' => UnidadMedida::factory(),
            'costo_compra' => $costoCompra,
            'costo_flete' => $costoFlete,
            'margen_ganancia' => $margen,
            'precio_venta' => $precioVenta,
            'stock_minimo' => 5,
            'activo' => true,
        ];
    }
}