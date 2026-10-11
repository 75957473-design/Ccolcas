<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoPedido extends Model
{
    use HasFactory;

    protected $table = 'estados_pedido';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'estado_id');
    }
}