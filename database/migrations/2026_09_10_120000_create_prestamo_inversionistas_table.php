<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestamo_inversionistas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->decimal('tasa', 6, 4)->default(0.05); // lo que gana el inversionista mensual
            $table->string('telefono')->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestamo_inversionistas');
    }
};
