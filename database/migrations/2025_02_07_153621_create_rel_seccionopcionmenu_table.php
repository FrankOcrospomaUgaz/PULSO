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
        Schema::create('rel_seccionopcionmenu', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seccion_id');
            $table->unsignedBigInteger('opcionmenu_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('seccion_id')->references('id')->on('secciones')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('opcionmenu_id')->references('id')->on('menuopciones')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rel_seccionopcionmenu');
    }
};
