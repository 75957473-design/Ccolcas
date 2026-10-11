<?php

namespace Database\Factories;

use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompraFactory extends Factory
{
    protected $model = Compra::class;

    public function definition(): array
    {
        return [
            'proveedor_id' => Proveedor::factory(),
            'usuario_id' => Usuario::factory(),
            'numero_comprobante' => $this->faker->numerify('F001-####'),
            'fecha_compra' => now(),
            'total' => $this->faker->randomFloat(2, 100, 5000),
            'observaciones' => $this->faker->sentence(),
        ];
    }
}