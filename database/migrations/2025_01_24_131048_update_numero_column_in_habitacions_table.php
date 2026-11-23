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
        Schema::table('habitacion', function (Blueprint $table) {
            // Cambiar el tipo de la columna 'numero' a varchar(50)
            $table->string('numero', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('habitacion', function (Blueprint $table) {
            // Revertir el tipo de la columna 'numero' a int
            $table->integer('numero')->change();
        });
    }
};
