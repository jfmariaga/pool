<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // limpiar posibles duplicados dejando el registro más antiguo
        $dups = DB::table('prestamo_clientes')
            ->select('cedula')
            ->whereNotNull('cedula')
            ->groupBy('cedula')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('cedula');

        foreach ($dups as $cedula) {
            $ids = DB::table('prestamo_clientes')->where('cedula', $cedula)->orderBy('id')->pluck('id');
            $keep = $ids->shift();
            DB::table('prestamos')->whereIn('cliente_id', $ids)->update(['cliente_id' => $keep]);
            DB::table('prestamo_clientes')->whereIn('id', $ids)->delete();
        }

        Schema::table('prestamo_clientes', function (Blueprint $table) {
            $table->dropIndex(['cedula']);
            $table->unique('cedula');
        });
    }

    public function down(): void
    {
        Schema::table('prestamo_clientes', function (Blueprint $table) {
            $table->dropUnique(['cedula']);
            $table->index('cedula');
        });
    }
};
