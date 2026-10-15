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
        Schema::table('det_compras', function (Blueprint $table) {
            $table->foreignId('socio_id')->nullable()->after('producto_id')->constrained('socios')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('det_compras', function (Blueprint $table) {
            $table->dropConstrainedForeignId('socio_id');
        });
    }
};
