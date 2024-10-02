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
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->nullable()->constrained('ventas');
            $table->foreignId('compra_id')->nullable()->constrained('compras');
            $table->foreignId('cuenta_id')->constrained('cuentas');
            $table->foreignId('usuario_id')->constrained('users');
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->bigInteger('monto');
            $table->date('fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
