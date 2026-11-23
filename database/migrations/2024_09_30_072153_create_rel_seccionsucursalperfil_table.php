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
        Schema::create('rel_seccionsucursalperfil', function (Blueprint $table) {
            $table->id(); // BIGINT AUTO_INCREMENT
            $table->unsignedBigInteger('perfil_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->unsignedBigInteger('seccion_id');
            $table->unsignedBigInteger('menuopcionesdefecto_id')->nullable(); // Campo opcional
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            // Foreign key constraints
            $table->foreign('menuopcionesdefecto_id')
                ->references('id')
                ->on('menuopciones')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('perfil_id')
                ->references('id')
                ->on('perfiles')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('seccion_id')
                ->references('id')
                ->on('secciones')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('sucursal_id')
                ->references('id')
                ->on('sucursal')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rel_seccionsucursalperfil');
    }
};
