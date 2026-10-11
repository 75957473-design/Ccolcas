<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoriaFactory extends Factory
{
    protected $model = Categoria::class;

    public function definition(): array
    {
        // Evita unique()->randomElement(6) que agota Faker tras 6 creates y rompe ProductoModelTest
        $base = $this->faker->randomElement([
            'Detergentes', 'Desinfectantes', 'Aromatizantes', 'Limpiadores', 'Jabones', 'Blanqueadores',
        ]);

        return [
            'nombre' => $base . ' ' . $this->faker->unique()->numerify('###'),
            'slug' => Str::slug($base) . '-' . $this->faker->unique()->numerify('###'),
            'activo' => true,
        ];
    }
}