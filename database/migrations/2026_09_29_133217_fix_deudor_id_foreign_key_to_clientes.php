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
        Schema::table('creditos', function (Blueprint $table) {
            $table->dropForeign(['deudor_id']);
            $table->foreign('deudor_id')->references('id')->on('clientes')->onDelete('cascade');
        });

        Schema::table('credito_temporal', function (Blueprint $table) {
            $table->dropForeign(['deudor_id']);
            $table->foreign('deudor_id')->references('id')->on('clientes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('creditos', function (Blueprint $table) {
            $table->dropForeign(['deudor_id']);
            $table->foreign('deudor_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('credito_temporal', function (Blueprint $table) {
            $table->dropForeign(['deudor_id']);
            $table->foreign('deudor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
