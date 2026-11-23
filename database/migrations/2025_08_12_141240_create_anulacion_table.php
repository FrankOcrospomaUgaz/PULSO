<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('anulacion', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('movimiento_id')->comment('ID del movimiento anulado');
        $table->unsignedBigInteger('usuario_id')->comment('ID del usuario que realizó la anulación');
        $table->dateTime('fecha')->comment('Fecha y hora de la anulación');

        $table->text('motivo')->comment('Motivo de la anulación');

        // Relaciones
        $table->foreign('movimiento_id')
              ->references('id')->on('movimiento')
              ->onDelete('cascade');

        $table->foreign('usuario_id')
              ->references('id')->on('users') 
              ->onDelete('cascade');

        $table->timestamps();
        $table->softDeletes();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anulacion');
    }
};
