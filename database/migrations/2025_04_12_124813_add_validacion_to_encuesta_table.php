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
        // Agregar columna 'validacion' a la tabla 'encuesta'
        Schema::table('encuesta', function (Blueprint $table) {
            $table->char('validacion', 1)->default('S')->comment('S = Sí, N = No');
        });
    
        // Modificar 'persona_id' en la tabla 'respuesta' para que acepte nulos
        Schema::table('respuesta', function (Blueprint $table) {
            $table->unsignedBigInteger('persona_id')->nullable()->change();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('encuesta', function (Blueprint $table) {
            $table->dropColumn('validacion');
        });
    }
    
};
