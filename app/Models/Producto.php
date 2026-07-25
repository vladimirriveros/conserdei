<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    /** @use HasFactory<\Database\Factories\ProductoFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'categoria_id',
        'codigo',
        'nombre',
        'marca',
        'descripcion',
        'imagen',
        'precio_compra',
        'precio_venta',
        'porcentaje_ganancia',
        'stock_minimo',
        'stock_maximo',
        'unidad_medida',
        'norma',
        'presion',
        'diametro',
        'estado',
    ];

    // Relación con historial de precios
    public function historialPrecios()
    {
        return $this->hasMany(HistorialPrecio::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
    public function lotes()
    {
        return $this->hasMany(Lote::class);
    }
    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class);
    }
    public function detalleCompras()
    {
        return $this->hasMany(DetalleCompra::class);
    }
    public function detalleSalidas()
    {
        return $this->hasMany(DetalleSalida::class);
    }
    public function historialStock()
    {
        return $this->hasMany(HistorialStockProducto::class);
    }


    public function historialPrecioVenta()
    {
        return $this->hasMany(HistorialPrecioVenta::class);
    }

}
