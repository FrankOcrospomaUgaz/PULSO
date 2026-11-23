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
        Schema::create('rel_operacionsucursalperfil', function (Blueprint $table) {
            $table->id(); // BIGINT AUTO_INCREMENT
            $table->boolean('situacion')->default(true); // Situación con valor por defecto 'E'
            $table->unsignedBigInteger('operacion_id');
            $table->unsignedBigInteger('tabla_id');
            $table->unsignedBigInteger('perfil_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            // Foreign key constraints
            $table->foreign('operacion_id')
                ->references('id')
                ->on('operaciones')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('tabla_id')
                ->references('id')
                ->on('tablas')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('perfil_id')
                ->references('id')
                ->on('perfiles')
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
        Schema::dropIfExists('rel_operacionsucursalperfil');
    }
};
