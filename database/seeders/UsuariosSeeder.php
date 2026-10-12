<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            ['nombre' => 'Admin CCOLCAS',      'email' => 'admin@ccolcas.com',      'rol' => 'Administrador'],
            ['nombre' => 'Gerente CCOLCAS',    'email' => 'gerente@ccolcas.com',    'rol' => 'Gerente'],
            ['nombre' => 'Subgerente CCOLCAS', 'email' => 'subgerente@ccolcas.com', 'rol' => 'Subgerente'],
            ['nombre' => 'Contador CCOLCAS',   'email' => 'contador@ccolcas.com',   'rol' => 'Contador'],
            ['nombre' => 'Almacenero CCOLCAS', 'email' => 'almacenero@ccolcas.com', 'rol' => 'Almacenero'],
            ['nombre' => 'Vendedor CCOLCAS',   'email' => 'vendedor@ccolcas.com',   'rol' => 'Vendedor'],
            ['nombre' => 'Cliente Demo',       'email' => 'cliente@ccolcas.com',    'rol' => 'Cliente'],
        ];

        foreach ($usuarios as $u) {
            $rol = Rol::where('nombre', $u['rol'])->first();
            if (! $rol) continue;

            Usuario::firstOrCreate(
                ['email' => $u['email']],
                [
                    'nombre_completo' => $u['nombre'],
                    'password'        => Hash::make('password'),
                    'rol_id'          => $rol->id,
                    'activo'          => true,
                ]
            );
        }
    }
}
