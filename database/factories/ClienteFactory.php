<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            'usuario_id' => null,
            'tipo_documento' => $this->faker->randomElement(['DNI', 'RUC']),
            'numero_documento' => $this->faker->unique()->numerify('###########'),
            'nombre_razon_social' => $this->faker->company(),
            'telefono' => $this->faker->numerify('9########'),
            'email' => $this->faker->unique()->safeEmail(),
            'direccion_entrega' => $this->faker->address(),
            'persona_recepcion' => $this->faker->name(),
        ];
    }
}