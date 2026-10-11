<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class PedidoFactory extends Factory
{
    protected $model = Pedido::class;

    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'estado_id' => EstadoPedido::factory(),
            'vendedor_id' => null,
            'tipo_comprobante_solicitado' => $this->faker->randomElement(['Boleta', 'Factura']),
            'fecha_pedido' => now(),
            'monto_total' => $this->faker->randomFloat(2, 50, 2000),
            'direccion_envio' => $this->faker->address(),
            'observaciones' => $this->faker->sentence(),
        ];
    }
}