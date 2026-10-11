<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockPorAlmacen extends Model
{
    use HasFactory;

    protected $table = 'stock_por_almacen';
    public $timestamps = false;

    protected $fillable = [
        'producto_id',
        'almacen_id',
        'cantidad',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }
}