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
        Schema::create('tarjeta', function (Blueprint $table) {
            $table->id(); // Campo id como llave primaria y autoincremental
            $table->string('descripcion'); // Campo para el nombre o descripción de la tarjeta
            $table->char('tipo', 1); // Campo de tipo que puede ser crédito o débito
            $table->integer('orden'); // Campo para indicar el orden
            $table->boolean('situacion')->default(true); // Situación con valor por defecto 'E'
            $table->string('icono')->nullable(); // Campo para la imagen que puede ser nulo
            $table->timestamps(); // Campos created_at y updated_at automáticos
            $table->softDeletes(); // deleted_at para eliminaciones lógicas

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarjeta');
    }
};
