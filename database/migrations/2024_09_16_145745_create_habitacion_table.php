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
        Schema::create('habitacion', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('numero');
            $table->string('situacion', 500);
            $table->unsignedInteger('piso_id');
            $table->foreign('piso_id', 'fk_habitacion_piso')
                ->references('id')
                ->on('piso')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->unsignedInteger('tipohabitacion_id');
            $table->foreign('tipohabitacion_id', 'fk_habitacion_tipohabitacion')
                ->references('id')
                ->on('tipohabitacion')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('habitacion');
    }
};
