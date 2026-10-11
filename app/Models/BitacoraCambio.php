<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BitacoraCambio extends Model
{
    use HasFactory;

    protected $table = 'bitacora_cambios';
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'tabla_afectada',
        'accion',
        'descripcion',
        'datos_previos',
        'datos_nuevos',
        'fecha',
    ];

    protected $casts = [
        'datos_previos' => 'array',
        'datos_nuevos' => 'array',
        'fecha' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}