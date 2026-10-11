<?php

namespace Database\Factories;

use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProveedorFactory extends Factory
{
    protected $model = Proveedor::class;

    public function definition(): array
    {
        return [
            'ruc' => $this->faker->unique()->numerify('20#########'),
            'nombre' => $this->faker->company(),
            'contacto' => $this->faker->name(),
            'telefono' => $this->faker->numerify('9########'),
            'email' => $this->faker->unique()->companyEmail(),
            'direccion' => $this->faker->address(),
        ];
    }
}