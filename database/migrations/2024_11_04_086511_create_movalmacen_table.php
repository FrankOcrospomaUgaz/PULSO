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
        Schema::create('movalmacen', function (Blueprint $table) {
            $table->id();
            $table->string('estado', 255)->default('FINALIZADO')->comment('PENDIENTE, ENVIADO, FINALIZADO, RECHAZADO');
            $table->unsignedBigInteger('movimiento_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('movimiento_id')->references('id')->on('movimiento')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('sucursal_id')->references('id')->on('sucursal')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movalmacen');
    }
};
