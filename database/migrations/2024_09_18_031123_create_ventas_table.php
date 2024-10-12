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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion');
            $table->decimal('monto_total', 15, 2)->default(0);
            $table->enum('estado', ['abierta', 'cerrada', 'cancelada'])->default('abierta');
            $table->foreignId('cuenta_id')->constrained('cuentas')->onDelete('cascade'); // Clave foránea de cuentas
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Clave foránea de usuarios
            $table->dateTime('fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
