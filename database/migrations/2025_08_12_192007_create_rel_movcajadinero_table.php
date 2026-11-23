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
        Schema::create('rel_movcajadinero', function (Blueprint $table) {
            $table->integer('cantidad')->default(0);
            $table->text('comentario');
            $table->unsignedBigInteger('dinero_id');
            $table->unsignedBigInteger('movcaja_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->timestamps();
            $table->softDeletes();

            // Índices y llaves foráneas
            $table->foreign('dinero_id')->references('id')->on('dinero')->onDelete('cascade');
            $table->foreign('movcaja_id')->references('id')->on('movcaja')->onDelete('cascade');
            $table->foreign('sucursal_id')->references('id')->on('sucursal')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rel_movcajadinero');
    }
};
