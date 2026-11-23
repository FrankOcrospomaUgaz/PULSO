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
        Schema::create('listaunidad', function (Blueprint $table) {
            $table->id();
            $table->decimal('formula', 24, 6);
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('unidad_id');
            $table->unsignedBigInteger('unidadbase_id');
            $table->unsignedBigInteger('empresa_id');
            $table->timestamps();
            $table->softDeletes();

            // Definición de claves foráneas
            $table->foreign('empresa_id')->references('id')->on('empresas')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('producto_id')->references('id')->on('producto')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('unidad_id')->references('id')->on('unidad')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('unidadbase_id')->references('id')->on('unidad')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listaunidad');
    }
};
