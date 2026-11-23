<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('tipodocumento', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255); // Campo para el nombre del documento
            $table->string('abreviatura', 50); // Campo para la abreviatura
            $table->enum('stock', ['Suma', 'Resta', 'No']);
            $table->unsignedBigInteger('tipomovimiento_id'); // Campo para el tipo con valores posibles 'Compra' o 'Venta'
             // Foreign key constraints
             $table->foreign('tipomovimiento_id')
             ->references('id')
             ->on('tipomovimiento')
             ->onUpdate('cascade')
             ->onDelete('cascade');
            $table->timestamps(); // Timestamps automáticos de Laravel
            
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipodocumento');
    }
};
