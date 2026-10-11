<?php

namespace Database\Factories;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    public function definition(): array
    {
        return [
            'nombre_completo' => $this->faker->name(),
            'rol_id' => Rol::factory(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'activo' => true,
        ];
    }
}