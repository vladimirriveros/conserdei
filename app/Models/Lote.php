<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Lote extends Model
{
    protected $table = 'lotes';

    protected $fillable = [
        'producto_id',
        'proveedor_id',
        'codigo_lote',
        'fecha_entrada',
        'fecha_vencimiento',
        'cantidad_inicial',
        'cantidad_actual',
        'precio_compra',
        'precio_venta',
        'estado',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function inventarioSucuralLotes()
    {
        return $this->hasMany(InventarioSucuralLote::class);
    }
    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class);
    }
     public function detalleCompras()
    {
        return $this->hasMany(DetalleCompra::class);
    }

    public function getFechaVencimientoAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }
    public function detalleSalidas()
    {
        return $this->hasMany(DetalleSalida::class);
    }

    public function getPrecioVentaCalculadoAttribute()
{
    // Si ya tiene un precio_venta guardado y es mayor a 0, usarlo
    if ($this->precio_venta && $this->precio_venta > 0) {
        return $this->precio_venta;
    }

    // Si no, calcularlo en tiempo real
    $tipoCambioActivo = TipoCambio::getActivo();
    $tipoCambioOficial = TipoCambio::getOficial();

    $precioBase = $this->precio_compra;

    if ($tipoCambioActivo && $tipoCambioOficial && $tipoCambioActivo->id != $tipoCambioOficial->id) {
        $factor = $tipoCambioActivo->precio_dolar / $tipoCambioOficial->precio_dolar;
        $precioBase = $this->precio_compra * $factor;
    }

    $porcentajeGanancia = $this->producto->porcentaje_ganancia ?? 30;

    return round($precioBase * (1 + $porcentajeGanancia / 100), 2);
}
}
