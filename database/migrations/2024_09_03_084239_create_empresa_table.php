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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id(); // Campo 'id' para el identificador único de la empresa.
            $table->string('razon_social', 255); // Campo 'razon_social' para el nombre de la empresa.
            $table->string('direccion', 255)->nullable(); // Campo 'direccion' que puede ser nulo.
            $table->string('ruc', 11)->unique(); // Campo 'ruc' con un máximo de 11 caracteres, único para cada empresa.
            $table->string('email', 255)->nullable(); // Campo 'email' que puede ser nulo.
            $table->string('telefono_fijo', 20)->nullable(); // Campo 'telefono_fijo' que puede ser nulo.
            $table->string('telefono_movil', 20)->nullable(); // Campo 'telefono_movil' que puede ser nulo.
            $table->string('logo')->nullable(); // Campo 'logo' para almacenar la URL o ruta del logo, puede ser nulo.
            $table->timestamps(); // Campos de fecha de creación y actualización.
            $table->softDeletes(); // Campo 'deleted_at' para borrado suave.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            //
        });
    }
};
