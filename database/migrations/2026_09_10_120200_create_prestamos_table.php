<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();
            $table->enum('modalidad', ['retroventa', 'personal']);

            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('inversionista_id')->nullable(); // retroventa
            $table->string('cartera')->nullable(); // personal: General / Laura / Mamá

            // datos de la prenda (retroventa)
            $table->string('num_contrato')->nullable();
            $table->decimal('peso', 10, 2)->nullable();
            $table->text('descripcion_prenda')->nullable();

            // fechas
            $table->date('fecha_inicio');
            $table->date('fecha_corte'); // desde cuándo corre el interés no pagado
            $table->date('fecha_cierre')->nullable();

            // dinero
            $table->decimal('monto', 15, 2); // capital inicial
            $table->decimal('saldo_capital', 15, 2); // capital vigente
            $table->decimal('tasa_interes', 6, 4); // mensual, la que paga el cliente
            $table->tinyInteger('plazo_meses')->default(4); // solo retroventa
            $table->decimal('saldo_interes_favor', 15, 2)->default(0);

            $table->enum('estado', ['activo', 'pagado', 'adjudicado'])->default('activo');
            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('prestamo_clientes')->onDelete('cascade');
            $table->foreign('inversionista_id')->references('id')->on('prestamo_inversionistas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestamos');
    }
};
