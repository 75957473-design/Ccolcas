<?php

namespace Database\Factories;

use App\Models\Almacen;
use App\Models\Producto;
use App\Models\StockPorAlmacen;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockPorAlmacenFactory extends Factory
{
    protected $model = StockPorAlmacen::class;

    public function definition(): array
    {
        return [
            'producto_id' => Producto::factory(),
            'almacen_id' => Almacen::factory(),
            'cantidad' => $this->faker->numberBetween(0, 100),
        ];
    }
}