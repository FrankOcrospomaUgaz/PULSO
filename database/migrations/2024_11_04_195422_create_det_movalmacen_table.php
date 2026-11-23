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
        Schema::create('det_movalmacen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movalmacen_id');
            $table->unsignedBigInteger('producto_id');
            $table->json('json_producto');
            $table->unsignedBigInteger('unidad_id');
            $table->json('json_listaunidad');
            $table->decimal('cantidad', 24, 6);
            $table->text('comentario');
            $table->string('situacion', 1)->default('E');
            $table->unsignedBigInteger('sucursal_id');
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('movalmacen_id')->references('id')->on('movalmacen')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('sucursal_id')->references('id')->on('sucursal')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('det_movalmacen');
    }
};
