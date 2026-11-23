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
        Schema::create('rolpersona', function (Blueprint $table) {
            $table->unsignedBigInteger('persona_id'); // Relación con la tabla 'persona'
            $table->unsignedBigInteger('rol_id'); // Relación con la tabla 'rol'
            $table->unsignedBigInteger('sucursal_id'); // Relación con la tabla 'sucursal'
            $table->timestamps(); // created_at y updated_at
            $table->softDeletes(); // deleted_at para eliminaciones lógicas

            // Definición de claves foráneas
            $table->foreign('persona_id')->references('id')->on('persona')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('rol_id')->references('id')->on('rol')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('sucursal_id')->references('id')->on('sucursal')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rolpersona');
    }
};
