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
        Schema::create('parametros', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('descripcion', 255);
            $table->string('valor', 255);
            $table->string('tipo', 255)->default('EMPRESA');
            $table->string('obligatorio', 1)->default('N');
            $table->boolean('estado', 1)->default(true);
            $table->unsignedBigInteger('categoriaparametro_id');
            $table->unsignedBigInteger('tabla_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            // Llaves foráneas
            $table->foreign('categoriaparametro_id')->references('id')->on('categoriaparametros')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('tabla_id')->references('id')->on('tablas')->onUpdate('cascade')->onDelete('cascade');
        });

    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parametros', function (Blueprint $table) {
            //
        });
    }
};
