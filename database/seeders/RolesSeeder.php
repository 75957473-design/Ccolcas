<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Administrador' => 'Administración general del sistema.',
            'Gerente' => 'Supervisión de las operaciones del negocio.',
            'Subgerente' => 'Apoyo en la administración y supervisión.',
            'Contador' => 'Administración de precios y operaciones comerciales.',
            'Almacenero' => 'Gestión de almacén e inventario.',
            'Vendedor' => 'Atención de ventas y pedidos.',
            'Cliente' => 'Usuario registrado para realizar pedidos.',
        ];

        foreach ($roles as $nombre => $descripcion) {
            Rol::firstOrCreate(
                ['nombre' => $nombre],
                ['descripcion' => $descripcion]
            );
        }
    }
}