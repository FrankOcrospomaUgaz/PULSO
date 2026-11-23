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
        Schema::create('rel_listaunidadsucursal', function (Blueprint $table) {
            $table->id();
            $table->decimal('preciocompra', 24, 6);
            $table->decimal('preciomanoobra', 24, 6);
            $table->decimal('precioventa', 24, 6);
            $table->string('moneda', 255)->default('PEN');
            $table->unsignedBigInteger('listaunidad_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->timestamps();
            $table->softDeletes();

            // Definición de claves foráneas
            $table->foreign('listaunidad_id')->references('id')->on('listaunidad')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('sucursal_id')->references('id')->on('sucursal')->onUpdate('cascade')->onDelete('cascade');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rel_listaunidadsucursal');
    }
};
