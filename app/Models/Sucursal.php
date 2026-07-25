<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    /** @use HasFactory<\Database\Factories\SucursalFactory> */
    use HasFactory;

    protected $table = 'sucursals';

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'activa',
    ];

    public function inventarioSucuralLotes()
    {
        return $this->hasMany(InventarioSucuralLote::class);
    }
    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class);
    }
    public function getTotalInventarioAttribute()
    {
        return $this->inventarioSucuralLotes()->sum('cantidad_en_sucursal');
    }
}
