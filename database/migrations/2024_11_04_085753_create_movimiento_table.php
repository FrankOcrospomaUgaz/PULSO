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
        Schema::create('movimiento', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 255);
            $table->timestamp('fecha');
            $table->unsignedBigInteger('usuario_id');
            $table->string('usuario', 255);
            $table->unsignedBigInteger('persona_id')->nullable();
            $table->string('persona', 255);
            $table->unsignedBigInteger('responsable_id');
            $table->string('responsable', 255);
            $table->text('comentario');
            $table->string('situacion', 1)->default('A');
            $table->unsignedBigInteger('tipomovimiento_id');
            $table->unsignedBigInteger('tipodocumento_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->unsignedBigInteger('movimiento_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Llaves foráneas
            $table->foreign('movimiento_id')->references('id')->on('movimiento')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('sucursal_id')->references('id')->on('sucursal')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('tipodocumento_id')->references('id')->on('tipodocumento')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('tipomovimiento_id')->references('id')->on('tipomovimiento')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimiento');
    }
};
