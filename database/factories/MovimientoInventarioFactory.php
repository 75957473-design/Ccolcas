<?php

namespace Database\Factories;

use App\Models\Almacen;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\TipoMovimiento;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovimientoInventarioFactory extends Factory
{
    protected $model = MovimientoInventario::class;

    public function definition(): array
    {
        return [
            'producto_id' => Producto::factory(),
            'almacen_id' => Almacen::factory(),
            'tipo_movimiento_id' => TipoMovimiento::factory(),
            'cantidad' => $this->faker->numberBetween(1, 50),
            'motivo' => $this->faker->sentence(),
            'usuario_id' => Usuario::factory(),
            'fecha_movimiento' => now(),
        ];
    }
}