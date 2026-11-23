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
    Schema::table('piso', function (Blueprint $table) {
        // Agregar la columna 'edificio_id' que acepta valores nulos
        $table->foreignId('edificio_id')->nullable()->constrained('edificio')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('piso', function (Blueprint $table) {
        // Eliminar la columna 'edificio_id'
        $table->dropColumn('edificio_id');
    });
}

};
