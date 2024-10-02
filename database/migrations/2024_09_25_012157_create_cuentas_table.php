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
        Schema::create('cuentas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Nombre de la cuenta bancaria
            $table->string('numero_de_cuenta')->nullable()->unique(); // Número de cuenta (único y puede ser nulo)
            $table->boolean('status'); // Estatus como booleano (1: activo, 0: inactivo)
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuentas');
    }
};
