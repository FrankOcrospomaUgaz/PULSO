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
        Schema::create('rel_categoriasucursal', function (Blueprint $table) {
            $table->id();
            $table->string('tipocarta', 255)->comment('PLATOS A LA CARTA');
            $table->string('situacion', 1)->default('E');
            $table->unsignedBigInteger('categoria_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->timestamps();
            $table->softDeletes();

            // Definición de claves foráneas
            $table->foreign('categoria_id')->references('id')->on('categoria')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('sucursal_id')->references('id')->on('sucursal')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rel_categoriasucursal');
    }
};
