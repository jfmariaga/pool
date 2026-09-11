<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestamo_movimientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prestamo_id');
            $table->date('fecha');
            $table->enum('tipo', [
                'desembolso',
                'pago_interes',
                'abono_capital',
                'mixto',
                'adjudicacion',
                'cancelacion',
            ]);

            $table->decimal('monto_interes', 15, 2)->default(0);
            $table->decimal('monto_capital', 15, 2)->default(0);
            $table->decimal('capital_antes', 15, 2)->default(0);
            $table->decimal('capital_despues', 15, 2)->default(0);
            $table->date('fecha_corte_antes')->nullable();
            $table->date('fecha_corte_despues')->nullable();
            $table->tinyInteger('meses_cubiertos')->default(0);
            $table->decimal('interes_causado', 15, 2)->default(0); // interés que se debía al momento del movimiento

            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('prestamo_id')->references('id')->on('prestamos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestamo_movimientos');
    }
};
