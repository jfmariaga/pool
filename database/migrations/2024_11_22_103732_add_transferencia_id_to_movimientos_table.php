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
        Schema::table('movimientos', function (Blueprint $table) {
            $table->unsignedBigInteger('transferencia_id')->nullable()->after('id'); // Campo transferencia_id
            $table->foreign('transferencia_id') // Clave foránea referenciando la misma tabla
                  ->references('id')
                  ->on('movimientos')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->dropForeign(['transferencia_id']); // Eliminar la relación
            $table->dropColumn('transferencia_id'); // Eliminar el campo
        });
    }
};
