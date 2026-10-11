<?php

namespace Database\Seeders;

use App\Models\EstadoPedido;
use Illuminate\Database\Seeder;

class EstadosPedidoSeeder extends Seeder
{
    public function run(): void
    {
        $estados = ['Pendiente', 'Confirmado', 'En Almacén', 'Enviado', 'Entregado', 'Cancelado'];

        foreach ($estados as $nombre) {
            EstadoPedido::firstOrCreate(['nombre' => $nombre]);
        }
    }
}
