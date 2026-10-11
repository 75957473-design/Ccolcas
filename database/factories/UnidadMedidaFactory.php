<?php

namespace Database\Factories;

use App\Models\UnidadMedida;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnidadMedidaFactory extends Factory
{
    protected $model = UnidadMedida::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->randomElement(['Unidad', 'Caja', 'Paquete', 'Galón', 'Litro', 'Kilogramo']),
            'simbolo' => $this->faker->randomElement(['und', 'cja', 'pqt', 'gal', 'L', 'kg']),
        ];
    }
}