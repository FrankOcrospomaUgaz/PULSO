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
        Schema::create('persona', function (Blueprint $table) {
            $table->id(); // Columna id con auto-incremento
            $table->string('nombres', 255);
            $table->string('apellidos', 255)->nullable();
            $table->string('direccion', 255);
            $table->string('email', 255);
            $table->string('telefonofijo', 10);
            $table->string('telefonomovil', 12);
            $table->text('imagen');
            $table->string('compartido', 1)->default('N'); // Campo compartido con valor por defecto 'N'
            $table->bigInteger('personamaestro_id')->unsigned();
            $table->bigInteger('sucursal_id')->unsigned();
            $table->bigInteger('distrito_id')->unsigned();
            $table->timestamps(); // created_at y updated_at
            $table->softDeletes(); // deleted_at para eliminaciones lógicas

            // Definición de claves foráneas
            $table->foreign('distrito_id')->references('id')->on('ubigeo')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('personamaestro_id')->references('id')->on('personamaestro')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('sucursal_id')->references('id')->on('sucursal')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persona');
    }
};
