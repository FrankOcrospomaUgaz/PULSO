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
        Schema::create('tipoopciones', function (Blueprint $table) {
            $table->id(); // Crea una columna 'id' tipo BIGINT con auto incremento
            $table->string('descripcion', 255); // Columna 'descripcion' tipo VARCHAR(255)
            $table->string('abreviatura', 20); // Columna 'abreviatura' tipo VARCHAR(20)
            $table->integer('orden')->default(0); // Columna 'orden' tipo INTEGER con valor por defecto 0
            $table->text('icono'); // Columna 'icono' tipo TEXT
            $table->boolean('estado'); // Columna 'icono' tipo TEXT
            $table->timestamp('created_at')->nullable(); // Columna 'created_at' tipo TIMESTAMP, permitiendo valores nulos
            $table->timestamp('updated_at')->nullable(); // Columna 'updated_at' tipo TIMESTAMP, permitiendo valores nulos
            $table->timestamp('deleted_at')->nullable(); // Columna 'deleted_at' tipo TIMESTAMP, permitiendo valores nulos

        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipoopciones', function (Blueprint $table) {
            //
        });
    }
};
