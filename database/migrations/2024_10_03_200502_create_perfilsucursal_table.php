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
        Schema::create('perfilsucursal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('perfil_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
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
        Schema::dropIfExists('perfilsucursal');
    }
};
