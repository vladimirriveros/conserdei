<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleSalida extends Model
{
    protected $table = 'detalle_salidas';

    protected $fillable = [
        'salida_id',
        'producto_id',
        'lote_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    public function salida()
    {
        return $this->belongsTo(Salida::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }
}
