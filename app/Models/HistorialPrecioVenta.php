<?php
// app/Models/HistorialPrecioVenta.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialPrecioVenta extends Model
{
    use HasFactory;

    protected $table = 'historial_precio_ventas';

    protected $fillable = [
        'producto_id',
        'precio_venta_anterior',
        'precio_venta_nuevo',
        'tipo_cambio_aplicado',
        'user_id',
        'motivo',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
