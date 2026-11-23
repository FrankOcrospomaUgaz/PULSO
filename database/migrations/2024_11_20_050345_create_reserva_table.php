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
        Schema::create('reserva', function (Blueprint $table) {
            $table->id();
            $table->timestamp('fecha')->notNullable();
            $table->timestamp('fechasalida')->nullable();
            $table->string('observacion', 200)->nullable();
            $table->string('situacion', 1)->default('A');
            $table->unsignedBigInteger('persona_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->unsignedBigInteger('habitacion_id');
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('persona_id')->references('id')->on('persona')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('sucursal_id')->references('id')->on('sucursal')->onUpdate('cascade');
            $table->foreign('habitacion_id')->references('id')->on('habitacion')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reserva');
    }
};
