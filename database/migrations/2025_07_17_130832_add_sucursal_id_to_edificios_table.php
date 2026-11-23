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
    Schema::table('edificio', function (Blueprint $table) {
        // Agregar la columna 'edificio_id' que acepta valores nulos
        $table->foreignId('sucursal_id')->nullable()->constrained('sucursal');
    });
}

public function down()
{
    Schema::table('edificio', function (Blueprint $table) {
        // Eliminar la columna 'edificio_id'
        $table->dropColumn('sucursal_id');
    });
}

};
