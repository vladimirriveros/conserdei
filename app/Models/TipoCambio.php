<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoCambio extends Model
{
    use HasFactory;

    protected $table = 'tipo_cambios';

    protected $fillable = [
        'precio_dolar',
        'fecha',
        'estado',
        'is_oficial'
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'estado' => 'boolean',
        'is_oficial' => 'boolean'
    ];

    // Obtener el tipo de cambio oficial
    public static function getOficial()
    {
        return self::where('is_oficial', true)->first();
    }

    // Obtener el tipo de cambio activo (para ventas)
    public static function getActivo()
    {
        return self::where('estado', true)->first();
    }

    public static function calcularTipoCambioAplicado($precioVenta, $precioCompra, $porcentajeGanancia, $tcOficial)
    {
        if ($precioCompra <= 0) {
            return $tcOficial; // Fallback seguro
        }

        $denominador = $precioCompra * (1 + $porcentajeGanancia / 100);

        if ($denominador <= 0) {
            return $tcOficial;
        }

        return round(($precioVenta * $tcOficial) / $denominador, 4);
    }
}
