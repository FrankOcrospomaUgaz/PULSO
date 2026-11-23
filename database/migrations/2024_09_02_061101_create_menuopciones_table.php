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
        Schema::create('menuopciones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('descripcion', 255);
            $table->integer('orden')->default(0);
            $table->text('icono');
            $table->string('accion', 255)->comment('Nombre de ruta');
            $table->bigInteger('tabla_id')->unsigned();
            $table->bigInteger('modulo_id')->unsigned();
            $table->bigInteger('tipoopcion_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();
            $table->char('acceso_rapido', 1)->default('N')->comment('S => Sí, N => No');
            $table->json('condiciones_ar')->default(json_encode([]));

            // Definición de claves foráneas
            $table->foreign('modulo_id')->references('id')->on('modulos')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('tabla_id')->references('id')->on('tablas')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('tipoopcion_id')->references('id')->on('tipoopciones')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menuopciones', function (Blueprint $table) {
            //
        });
    }
};
