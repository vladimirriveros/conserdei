<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialPrecio extends Model
{
    use HasFactory;

    protected $table = 'historial_precios';

    protected $fillable = [
        'producto_id',
        'compra_id',
        'user_id',
        'precio_anterior',
        'precio_nuevo',
        'motivo',
        'observaciones'
    ];

    protected $casts = [
        'precio_anterior' => 'decimal:2',
        'precio_nuevo' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scope para filtrar por producto
    public function scopePorProducto($query, $productoId)
    {
        return $query->where('producto_id', $productoId);
    }

    // Scope para filtrar por rango de fechas
    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);
    }

    // Formatear precio anterior
    public function getPrecioAnteriorFormateadoAttribute()
    {
        return '$ ' . number_format($this->precio_anterior, 2);
    }

    // Formatear precio nuevo
    public function getPrecioNuevoFormateadoAttribute()
    {
        return '$ ' . number_format($this->precio_nuevo, 2);
    }

    // Obtener diferencia
    public function getDiferenciaAttribute()
    {
        return $this->precio_nuevo - $this->precio_anterior;
    }

    // Obtener porcentaje de cambio
    public function getPorcentajeCambioAttribute()
    {
        if ($this->precio_anterior == 0) return 100;
        return round(($this->diferencia / $this->precio_anterior) * 100, 2);
    }
}
