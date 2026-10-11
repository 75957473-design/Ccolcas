<?php

namespace Database\Factories;

use App\Models\EstadoPedido;
use Illuminate\Database\Eloquent\Factories\Factory;

class EstadoPedidoFactory extends Factory
{
    protected $model = EstadoPedido::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->randomElement([
                'Pendiente', 'Confirmado', 'En Almacén', 'Enviado', 'Entregado', 'Cancelado',
            ]),
        ];
    }
}