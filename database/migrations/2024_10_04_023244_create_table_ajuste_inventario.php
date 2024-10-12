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
        Schema::create('ajuste_inventario', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->integer('cantidades_positivas')->nullable();
            $table->integer('cantidades_negativas')->nullable();
            $table->integer('count_productos')->nullable();
            $table->foreignId('user_id')->constrained('users');
        });
        Schema::create('det_ajuste_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ajuste_id')->constrained('ajuste_inventario');
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('det_compra_id')->constrained('det_compras');
            $table->integer('cant_sistema');
            $table->integer('cant_real');
            $table->integer('cant_ajustada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ajuste_inventario');
        Schema::dropIfExists('det_ajuste_inventario');
    }
};
