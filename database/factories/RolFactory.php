<?php

namespace Database\Factories;

use App\Models\Rol;
use Illuminate\Database\Eloquent\Factories\Factory;

class RolFactory extends Factory
{
    protected $model = Rol::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->randomElement(['admin', 'vendedor', 'almacen', 'cliente']),
            'descripcion' => $this->faker->sentence(),
        ];
    }
}