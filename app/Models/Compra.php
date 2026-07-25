<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compra extends Model
{
    use SoftDeletes;
    protected $table = 'compras';

    protected $fillable = [
        'proveedor_id',
        'fecha',
        'total',
        'estado',
        'observaciones',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCompra::class);
    }
}
