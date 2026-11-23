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
        Schema::create('rol', function (Blueprint $table) {
            $table->id(); // Clave primaria autoincremental
            $table->string('descripcion', 255); // Descripción del rol
            $table->timestamps(); // created_at y updated_at
            $table->softDeletes(); // Campo deleted_at para eliminaciones lógicas

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rol');
    }
};
