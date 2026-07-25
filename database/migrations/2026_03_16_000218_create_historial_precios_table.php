<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('historial_precios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('compra_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->decimal('precio_anterior', 10, 2);
            $table->decimal('precio_nuevo', 10, 2);
            $table->string('motivo')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');
            $table->foreign('compra_id')->references('id')->on('compras')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users');

            $table->index('producto_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('historial_precios');
    }
};
