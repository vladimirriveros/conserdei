<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialStockProducto extends Model
{
    use HasFactory;

    protected $table = 'historial_stock_productos';

    protected $fillable = [
        'producto_id',
        'stock_minimo_anterior',
        'stock_minimo_nuevo',
        'stock_maximo_anterior',
        'stock_maximo_nuevo',
        'user_id',
        'motivo',
        'observaciones'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
