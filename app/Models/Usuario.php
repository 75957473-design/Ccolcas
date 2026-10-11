<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre_completo',
        'rol_id',
        'email',
        'password',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'password' => 'hashed',
    ];

    // Relaciones
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'usuario_id');
    }

    // Métodos para verificar Roles de CCOLCAS
    public function esAdmin(): bool
    {
        return $this->rol && $this->rol->nombre === 'Administrador';
    }

    public function esGerente(): bool
    {
        return $this->rol && ($this->rol->nombre === 'Gerente' || $this->rol->nombre === 'Administrador');
    }

    public function esAlmacenero(): bool
    {
        return $this->rol && $this->rol->nombre === 'Almacenero';
    }

    public function esVendedor(): bool
    {
        return $this->rol && $this->rol->nombre === 'Vendedor';
    }

    public function esContador(): bool
    {
        return $this->rol && $this->rol->nombre === 'Contador';
    }

    public function esSubgerente(): bool
    {
        return $this->rol && $this->rol->nombre === 'Subgerente';
    }

    public function esCliente(): bool
    {
        return $this->rol && $this->rol->nombre === 'Cliente';
    }

    public function esPersonal(): bool
    {
        return $this->rol && $this->rol->nombre !== 'Cliente';
    }
}