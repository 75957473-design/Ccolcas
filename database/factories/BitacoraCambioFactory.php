<?php

namespace Database\Factories;

use App\Models\BitacoraCambio;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class BitacoraCambioFactory extends Factory
{
    protected $model = BitacoraCambio::class;

    public function definition(): array
    {
        return [
            'usuario_id' => Usuario::factory(),
            'tabla_afectada' => $this->faker->randomElement(['productos', 'pedidos', 'clientes', 'ventas']),
            'accion' => $this->faker->randomElement(['INSERT', 'UPDATE', 'DELETE']),
            'descripcion' => $this->faker->sentence(),
            'fecha' => now(),
        ];
    }
}