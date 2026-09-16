<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La columna `cartera` (texto) quedó reemplazada por `cartera_id` + la relación
     * `Prestamo::cartera()`. Mantener ambas con el mismo nombre "cartera" es un choque:
     * Eloquent siempre prioriza el atributo de la columna sobre la relación del mismo
     * nombre, así que `$prestamo->cartera` nunca devolvía el catálogo mientras la
     * columna string seguía existiendo.
     */
    public function up(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            $table->dropColumn('cartera');
        });
    }

    public function down(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            $table->string('cartera')->nullable()->after('inversionista_id');
        });
    }
};
