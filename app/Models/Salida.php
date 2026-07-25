<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salida extends Model
{
    use SoftDeletes;
    protected $table = 'salidas';

    protected $fillable = [
        'sucursal_id',
        'fecha',
        'motivo',
        'total',
        'estado',
        'observaciones',
    ];

    // 👇 VERIFICAR QUE ESTA RELACIÓN EXISTA
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function detalles()
    {
        return $this->hasMany(DetalleSalida::class, 'salida_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id'); // Asegúrate de que 'user_id' sea el usuario correcto
    }
}
