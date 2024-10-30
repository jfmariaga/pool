<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('venta_cuenta_temporal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_temporal_id')->constrained('ventas_temporales')->onDelete('cascade');
            $table->foreignId('cuenta_id')->constrained('cuentas')->onDelete('cascade');
            $table->decimal('monto', 15, 2);
            $table->timestamps(); // Para tener registros de cuándo se creó y actualizó
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('venta_cuenta_temporal');
    }
};
