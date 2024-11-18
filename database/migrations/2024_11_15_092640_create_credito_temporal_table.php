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
        Schema::create('credito_temporal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deudor_id');
            $table->unsignedBigInteger('responsable_id');
            $table->foreignId('venta_temporal_id')->constrained('ventas_temporales')->onDelete('cascade');
            $table->decimal('monto', 15, 2);

            $table->foreign('deudor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('responsable_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credito_temporal');
    }
};
