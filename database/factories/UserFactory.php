<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Genera datos de prueba para crear instancias del modelo User.
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /** Contraseña cifrada reutilizada por las instancias generadas en el proceso. */
    protected static ?string $password;

    /**
     * Define los datos predeterminados de un usuario de prueba.
     *
     * El correo se genera de forma única y la contraseña se cifra antes de guardarla.
     *
     * @return array<string, mixed> Atributos iniciales del usuario.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /** Devuelve un estado de fábrica para crear un usuario con el correo sin verificar. */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
