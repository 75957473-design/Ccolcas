<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'imagen',
        'categoria_id',
        'unidad_medida_id',
        'costo_compra',
        'costo_flete',
        'margen_ganancia',
        'precio_venta',
        'stock_minimo',
        'activo',
    ];

    protected $casts = [
        'costo_compra'    => 'decimal:2',
        'costo_flete'     => 'decimal:2',
        'margen_ganancia' => 'decimal:2',
        'precio_venta'    => 'decimal:2',
        'stock_minimo'    => 'integer',
        'activo'          => 'boolean',
    ];

    // Relaciones
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_medida_id');
    }

    public function stocks()
    {
        return $this->hasMany(StockPorAlmacen::class, 'producto_id');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'producto_id');
    }

    // Accessor para obtener la URL completa de la imagen
    public function getUrlImagenAttribute()
    {
        if ($this->imagen && str_starts_with($this->imagen, 'images/') && is_file(public_path($this->imagen))) {
            return asset($this->imagen);
        }

        if ($this->imagen && Storage::disk('public')->exists($this->imagen)) {
            return asset('storage/' . $this->imagen);
        }

        return asset('images/producto-default.svg');
    }

    // Métodos y Lógica de Negocio (Gestión de Precios e Inventario)
    public function calcularPrecioVenta(): float
    {
        $costoTotal = $this->costo_compra + $this->costo_flete;
        $precio = $costoTotal * (1 + ($this->margen_ganancia / 100));
        return round($precio, 2);
    }

    public function stockTotal(): int
    {
        if ($this->relationLoaded('stocks')) {
            return (int) $this->stocks->sum('cantidad');
        }

        return (int) $this->stocks()->sum('cantidad');
    }

    public function enStockCritico(): bool
    {
        return $this->stockTotal() <= $this->stock_minimo;
    }
}