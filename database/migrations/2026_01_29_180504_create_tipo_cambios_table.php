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
        Schema::create('tipo_cambios', function (Blueprint $table) {
            $table->id();

            $table->decimal('precio_dolar', 10, 2)->notNullable();
            $table->date('fecha');
            $table->boolean('estado')->default(true);
            $table->boolean('is_oficial')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_cambios');
    }
};
