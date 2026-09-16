<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            $table->unsignedBigInteger('cartera_id')->nullable()->after('cartera');
            $table->decimal('tasa_interes_cartera', 6, 4)->nullable()->after('tasa_interes_inversionista');

            $table->foreign('cartera_id')->references('id')->on('prestamo_carteras')->nullOnDelete();
        });

        // Migra los préstamos personales ya existentes (columna `cartera` como texto)
        // al nuevo catálogo, para que todos los registros (viejos y nuevos) queden
        // homogéneos bajo `cartera_id`.
        $carteras = DB::table('prestamo_carteras')->pluck('id', 'nombre');
        foreach ($carteras as $nombre => $id) {
            DB::table('prestamos')
                ->where('modalidad', 'personal')
                ->where('cartera', $nombre)
                ->update(['cartera_id' => $id]);
        }
    }

    public function down(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            $table->dropForeign(['cartera_id']);
            $table->dropColumn(['cartera_id', 'tasa_interes_cartera']);
        });
    }
};
