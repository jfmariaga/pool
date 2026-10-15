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
        Schema::table('ventas_temporales', function (Blueprint $table) {
            $table->boolean('bajo_negociacion')->default(false)->after('monto_total');
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->boolean('bajo_negociacion')->default(false)->after('monto_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas_temporales', function (Blueprint $table) {
            $table->dropColumn('bajo_negociacion');
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn('bajo_negociacion');
        });
    }
};
