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
        Schema::create('creditos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deudor_id');
            $table->unsignedBigInteger('responsable_id');
            $table->unsignedBigInteger('venta_id')->nullable();
            $table->string('tipo')->nullable();
            $table->decimal('monto', 15, 2);
            $table->date('fecha');
            $table->enum('estado', ['pendiente', 'pago'])->default('pendiente');
            $table->tinyInteger('block')->nullable();
            $table->timestamps();

            // Llaves foráneas
            $table->foreign('deudor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('responsable_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('venta_id')->references('id')->on('ventas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creditos');
    }
};
