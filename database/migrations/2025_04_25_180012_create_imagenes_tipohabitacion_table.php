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
        Schema::create('imagenes_tipohabitacion', function (Blueprint $table) {
            $table->id();  // Identificador único de la imagen
            $table->foreignId('tipohabitacion_id')  // Relación con la tabla 'tipohabitacion'
                  ->constrained('tipohabitacion')
                  ->onDelete('cascade');  // Si se elimina el tipo de habitación, se eliminan las imágenes asociadas
            $table->text('url');  // Cambio a 'text' para poder almacenar más caracteres
            $table->string('descripcion')->nullable();  // Descripción opcional para la imagen
            $table->timestamps();  // Marca de tiempo de creación y actualización
        });
    }
    


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imagenes_tipohabitacion');
    }
};
