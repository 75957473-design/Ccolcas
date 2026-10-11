<?php

namespace Database\Factories;

use App\Models\Almacen;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetallePedidoFactory extends Factory
{
    protected $model = DetallePedido::class;

    public function definition(): array
    {
        $cantidad = $this->faker->numberBetween(1, 20);
        $precio = $this->faker->randomFloat(2, 5, 100);

        return [
            'pedido_id' => Pedido::factory(),
            'producto_id' => Producto::factory(),
            'almacen_id' => Almacen::factory(),
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'subtotal' => round($cantidad * $precio, 2),
        ];
    }
}