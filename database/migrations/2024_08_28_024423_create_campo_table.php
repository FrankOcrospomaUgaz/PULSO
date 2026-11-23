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
        Schema::create('campos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('descripcion', 255);
            $table->string('comentario', 255);
            $table->integer('longitud')->default(0);
            $table->string('diccionario', 255);
            $table->string('validacion', 1)->default('N');
            $table->string('msgvalidacion', 255);
            $table->integer('longitudreporte')->default(0);
            $table->string('alineacionreporte', 1)->default('C');
            $table->bigInteger('tabla_id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            // Clave foránea
            $table->foreign('tabla_id')->references('id')->on('tablas')->onUpdate('cascade')->onDelete('cascade');
        });

        
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campo', function (Blueprint $table) {
            //
        });
    }
};
