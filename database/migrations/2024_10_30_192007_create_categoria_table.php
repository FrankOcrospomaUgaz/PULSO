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
        Schema::create('categoria', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion', 255);
            $table->string('abreviatura', 255);
            $table->integer('nivel')->default(1);
            $table->integer('orden')->default(0);
            $table->string('padre', 1)->default('N');
            $table->text('imagen');
            $table->unsignedBigInteger('categoria_id')->nullable(); // Clave foránea para relación con otra categoría
            $table->unsignedBigInteger('empresa_id'); // Clave foránea para empresa
            $table->timestamps();
            $table->softDeletes();

            // Definición de claves foráneas
            $table->foreign('categoria_id')->references('id')->on('categoria')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /** 
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categoria');
    }
};
