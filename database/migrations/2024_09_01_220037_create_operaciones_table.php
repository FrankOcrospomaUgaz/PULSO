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
        Schema::create('operaciones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tipo', 1)->default('T');
            $table->string('descripcion', 255);
            $table->string('comentario', 255);
            $table->string('accion', 255);
            $table->text('imagen');
            $table->string('color', 255);
            $table->text('parametros')->default('{}');
            $table->boolean('estado'); 
            $table->bigInteger('tabla_id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();


            // Clave foránea
            $table->foreign('tabla_id')
                  ->references('id')
                  ->on('tablas')  // Asegúrate de que esta tabla exista
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('operaciones');
    }
};
