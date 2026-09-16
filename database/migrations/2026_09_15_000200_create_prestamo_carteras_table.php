<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestamo_carteras', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->boolean('activo')->default(1);
            $table->timestamps();
        });

        // El % de cartera y el % de casa se definen por préstamo (igual que
        // tasa_interes_inversionista/tasa_interes_casa en retroventa), por eso este
        // catálogo no tiene columna de tasa.
        foreach (['General', 'Laura', 'Mamá'] as $nombre) {
            DB::table('prestamo_carteras')->insert([
                'nombre' => $nombre,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('prestamo_carteras');
    }
};
