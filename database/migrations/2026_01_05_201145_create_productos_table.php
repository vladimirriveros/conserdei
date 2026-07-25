<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->string('marca');
            $table->text('descripcion')->nullable();
            $table->text('imagen')->nullable();
            $table->decimal('precio_compra', 8, 2);
            $table->decimal('precio_venta', 8, 2);
            $table->decimal('porcentaje_ganancia', 5, 2)->default(0);
            $table->integer('stock_minimo')->default(0);
            $table->integer('stock_maximo')->default(0);
            $table->string('unidad_medida')->default('unidad');
            $table->boolean('estado')->default(false);

            $table->string('norma')->nullable();
            $table->string('presion')->nullable();
            $table->string('diametro')->nullable();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
