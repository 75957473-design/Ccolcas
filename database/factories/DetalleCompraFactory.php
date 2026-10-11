<?php

namespace Database\Factories;

use App\Models\Almacen;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetalleCompraFactory extends Factory
{
    protected $model = DetalleCompra::class;

    public function definition(): array
    {
        return [
            'compra_id' => Compra::factory(),
            'producto_id' => Producto::factory(),
            'almacen_id' => Almacen::factory(),
            'cantidad' => $this->faker->numberBetween(1, 50),
            'precio_unitario' => $this->faker->randomFloat(2, 5, 100),
            'fecha_vencimiento' => $this->faker->dateTimeBetween('+1 month', '+2 years'),
        ];
    }
}