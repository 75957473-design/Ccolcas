<?php

namespace Database\Factories;

use App\Models\TipoMovimiento;
use Illuminate\Database\Eloquent\Factories\Factory;

class TipoMovimientoFactory extends Factory
{
    protected $model = TipoMovimiento::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->randomElement([
                'Entrada por Compra', 'Salida por Venta', 'Ajuste', 'Merma',
            ]),
            'tipo' => $this->faker->randomElement(['ENTRADA', 'SALIDA']),
        ];
    }
}