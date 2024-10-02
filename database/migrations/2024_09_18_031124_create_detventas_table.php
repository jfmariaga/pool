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
        Schema::create('det_ventas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('venta_id')->constrained('ventas');
            $table->bigInteger('producto_id')->constrained('productos');
            $table->integer('cant');
            $table->integer('precio_venta');
            $table->bigInteger('det_compra_id')->constrained('det_compras');
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detventas');
    }
};
