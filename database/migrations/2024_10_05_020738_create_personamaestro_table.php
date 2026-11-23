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
        Schema::create('personamaestro', function (Blueprint $table) {
            $table->id(); // Columna id con auto-incremento
            $table->string('tipopersona', 255); // Tipo de persona (empresa/natural)
            $table->string('nombres', 255);
            $table->string('apellidos', 255);
            $table->string('nrodoc', 11); // Número de documento de identidad
            $table->string('sexo', 1)->default('M'); // Sexo, con valor predeterminado 'M'
            $table->date('fechanac')->nullable(); // Fecha de nacimiento, permitiendo valores nulos
            $table->timestamps(); // created_at y updated_at
            $table->softDeletes(); // deleted_at para eliminaciones lógicas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personamaestro');
    }
};
