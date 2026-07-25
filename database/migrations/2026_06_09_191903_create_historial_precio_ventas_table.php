<?php
// database/migrations/YYYY_MM_DD_HHMMSS_create_historial_precio_ventas_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_precio_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->decimal('precio_venta_anterior', 10, 2);
            $table->decimal('precio_venta_nuevo', 10, 2);
            $table->decimal('tipo_cambio_aplicado', 10, 4); // Ej: 6.9600, 10.2000
            $table->foreignId('user_id')->constrained('users');
            $table->string('motivo'); // Ej: "Actualización masiva por TC", "Edición manual de precio de venta"
            $table->timestamps();

            $table->index('producto_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_precio_ventas');
    }
};
