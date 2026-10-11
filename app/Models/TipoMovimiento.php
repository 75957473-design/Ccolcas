<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoMovimiento extends Model
{
    use HasFactory;

    protected $table = 'tipos_movimiento';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'tipo',
    ];

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'tipo_movimiento_id');
    }
}