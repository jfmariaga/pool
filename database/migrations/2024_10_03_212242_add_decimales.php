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
        Schema::table('det_compras', function (Blueprint $table) {
            $table->decimal('precio_compra', 15,2)->change();
        });
        Schema::table('compras', function (Blueprint $table) {
            $table->decimal('total', 15,2)->change();
        });
        Schema::table('ventas', function (Blueprint $table) {
            $table->decimal('total', 15,2)->nullable();
        });
        Schema::table('det_ventas', function (Blueprint $table) {
            $table->decimal('precio_venta', 15,2)->change();
        });
        Schema::table('movimientos', function (Blueprint $table) {
            $table->decimal('monto', 15,2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
