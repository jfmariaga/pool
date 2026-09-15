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
            $table->decimal('tasa_interes_inversionista', 6, 4)->nullable()->after('tasa_interes');
            $table->decimal('tasa_interes_casa', 6, 4)->nullable()->after('tasa_interes_inversionista');
        });

        // Backfill: preserva los intereses ya calculados de los contratos de retroventa
        // existentes. Antes el interés del inversionista salía de la tasa del catálogo
        // y el de la casa era el residuo (tasa_interes - tasa inversionista).
        $retro = DB::table('prestamos')
            ->join('prestamo_inversionistas', 'prestamos.inversionista_id', '=', 'prestamo_inversionistas.id')
            ->where('prestamos.modalidad', 'retroventa')
            ->select('prestamos.id', 'prestamos.tasa_interes', 'prestamo_inversionistas.tasa as tasa_inversionista')
            ->get();

        foreach ($retro as $p) {
            $tasaInv = (float) $p->tasa_inversionista;
            $tasaCasa = max(0, round((float) $p->tasa_interes - $tasaInv, 4));

            DB::table('prestamos')->where('id', $p->id)->update([
                'tasa_interes_inversionista' => $tasaInv,
                'tasa_interes_casa' => $tasaCasa,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            $table->dropColumn(['tasa_interes_inversionista', 'tasa_interes_casa']);
        });
    }
};
