<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('det_compra_socios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('det_compra_id')->constrained('det_compras')->cascadeOnDelete();
            $table->foreignId('socio_id')->constrained('socios')->cascadeOnDelete();
            $table->decimal('monto_aportado', 12, 2);
            $table->decimal('porcentaje', 6, 2);
        });

        // migramos las líneas que ya tenían un socio único asignado (socio_id) a la tabla de aportes,
        // cada una queda con el 100% de aporte, para que los cálculos de KPIs sigan siendo correctos
        $detalles = DB::table('det_compras')->whereNotNull('socio_id')->get();

        foreach ($detalles as $det) {
            DB::table('det_compra_socios')->insert([
                'det_compra_id'  => $det->id,
                'socio_id'       => $det->socio_id,
                'monto_aportado' => $det->precio_compra * $det->stock_compra,
                'porcentaje'     => 100,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('det_compra_socios');
    }
};
