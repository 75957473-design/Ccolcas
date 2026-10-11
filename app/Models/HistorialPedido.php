<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialPedido extends Model
{
    use HasFactory;

    protected $table = 'historial_pedido';

    protected $fillable = [
        'pedido_id',
        'estado_anterior_id',
        'estado_nuevo_id',
        'usuario_id',
        'observacion',
    ];

    public function pedido() { return $this->belongsTo(Pedido::class); }
    public function estadoAnterior() { return $this->belongsTo(EstadoPedido::class, 'estado_anterior_id'); }
    public function estadoNuevo() { return $this->belongsTo(EstadoPedido::class, 'estado_nuevo_id'); }
    public function usuario() { return $this->belongsTo(Usuario::class); }
}
