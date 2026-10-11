<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'cliente_id',
        'estado_id',
        'vendedor_id',
        'tipo_comprobante_solicitado',
        'fecha_pedido',
        'monto_total',
        'direccion_envio',
        'observaciones',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoPedido::class, 'estado_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(Usuario::class, 'vendedor_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }

    public function venta()
    {
        return $this->hasOne(Venta::class, 'pedido_id');
    }

    public function historial()
    {
        return $this->hasMany(HistorialPedido::class, 'pedido_id')->latest();
    }
}
