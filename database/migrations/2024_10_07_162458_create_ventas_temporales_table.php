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
        Schema::create('ventas_temporales', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion');
            $table->decimal('monto_total', 15, 2)->default(0);
            $table->enum('estado', ['abierta', 'cerrada', 'cancelada'])->default('abierta');
            $table->foreignId('cuenta_id')->nullable()->constrained('cuentas')->onDelete('cascade'); // Puede ser nula hasta que se seleccione una cuenta
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('fecha')->nullable(); // Fecha temporal
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas_temporales');
    }
};
