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
        Schema::create('cierre_caja', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->constrained('users');
            $table->dateTime('fecha');
            $table->bigInteger('ult_compra')->nullable();
            $table->bigInteger('ult_venta')->nullable();
            $table->bigInteger('ult_movimiento')->nullable();
            $table->decimal('total_inicio', 15,2)->nullable();
            $table->decimal('total_cierre', 15,2)->nullable();
            $table->decimal('total_ventas', 15,2)->nullable();
            $table->decimal('total_compras', 15,2)->nullable();
            $table->decimal('total_egresos', 15,2)->nullable();
            $table->decimal('total_ingresos', 15,2)->nullable();
        });

        Schema::create('det_cierre_caja', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('cierre_id')->constrained('cierre_caja');
            $table->bigInteger('cuenta_id')->constrained('cuentas');
            $table->decimal('total_inicio', 15,2)->nullable();
            $table->decimal('total_cierre', 15,2)->nullable();
            $table->decimal('total_ingresos', 15,2)->nullable();
            $table->decimal('total_egresos', 15,2)->nullable();

        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->tinyInteger('block')->nullable();
        });
        Schema::table('compras', function (Blueprint $table) {
            $table->tinyInteger('block')->nullable();
        });
        Schema::table('movimientos', function (Blueprint $table) {
            $table->tinyInteger('block')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cierre_caja');
        Schema::dropIfExists('det_cierre_caja');
    }
};
