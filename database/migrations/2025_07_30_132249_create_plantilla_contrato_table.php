<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlantillaContratoTable extends Migration
{
    public function up(): void
    {
        Schema::create('plantilla_contrato', function (Blueprint $table) {
            $table->id();

            // Nombre de la plantilla (ej. "Contrato de habitación", "Contrato de local comercial")
            $table->string('titulo');

            // Contenido HTML con TinyMCE y variables dinámicas (ej: {{nombre_inquilino}})
            $table->longText('contenido_html');

            // Descripción opcional para el usuario que la crea
            $table->text('descripcion')->nullable();

            // Información de control
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->unsignedBigInteger('tipo_ambiente_id')->nullable();
            $table->foreign('sucursal_id')->references('id')->on('sucursal');
            $table->foreign('tipo_ambiente_id')->references('id')->on('tipoambiente');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plantilla_contrato');
    }
}
