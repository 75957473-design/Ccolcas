<?php

namespace Database\Factories;

use App\Models\Pedido;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

class VentaFactory extends Factory
{
    protected $model = Venta::class;

    public function definition(): array
    {
        return [
            'pedido_id' => Pedido::factory(),
            'usuario_id' => Usuario::factory(),
            'tipo_comprobante' => $this->faker->randomElement(['Boleta', 'Factura', 'Nota de Venta']),
            'numero_comprobante' => $this->faker->numerify('B001-####'),
            'metodo_pago' => $this->faker->randomElement(['Efectivo', 'Transferencia', 'Yape/Plin']),
            'monto_pagado' => $this->faker->randomFloat(2, 50, 2000),
            'fecha_emision' => now(),
        ];
    }
}