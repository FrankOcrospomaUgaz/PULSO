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
        Schema::create('conceptopago', function (Blueprint $table) {
            $table->id(); // Campo autoincremental id
            $table->string('descripcion', 255); // Campo de descripción
            $table->char('tipo', 1); // Campo tipo, se puede ajustar el tamaño si es necesario
            $table->boolean('restringido')->default(false); // Campo restringido (booleano)
            $table->timestamps(); // Campos created_at y updated_at automáticos
            $table->softDeletes(); // deleted_at para eliminaciones lógicas

        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conceptopago');
    }
};
