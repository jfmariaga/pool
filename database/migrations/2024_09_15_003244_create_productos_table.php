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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('categoria_id')->constrained('categorias'); // Relación con tabla categorias
            $table->bigInteger('precio_base')->nullable();; // Precio base
            $table->bigInteger('precio_mayorista')->nullable(); // Precio mayorista
            $table->boolean('disponible')->default(true); // Disponible (true/false)
            $table->boolean('stock_infinito')->default(false); // Stock infinito (true/false)
            $table->text('descripcion')->nullable(); // Descripción del producto
            $table->text('imagenes')->nullable(); // Almacenamiento de imágenes en formato JSON
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
