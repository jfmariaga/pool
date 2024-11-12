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
        Schema::create('abonos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('credito_id'); // ID del crédito al que pertenece el abono
            $table->decimal('monto', 15, 2); // Monto del abono
            $table->date('fecha'); // Fecha del abono
            $table->timestamps();

            // Llave foránea
            $table->foreign('credito_id')->references('id')->on('creditos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abonos');
    }
};
