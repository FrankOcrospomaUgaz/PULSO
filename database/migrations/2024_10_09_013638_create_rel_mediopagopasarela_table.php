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
        Schema::create('rel_mediopagopasarela', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pasarela_id'); 
            $table->unsignedBigInteger('medio_id'); 
            $table->timestamps(); 
            $table->softDeletes();

            // Definición de claves foráneas
            $table->foreign('pasarela_id')->references('id')->on('pasarelapago')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('medio_id')->references('id')->on('mediopago')->onUpdate('cascade')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rel_mediopagopasarela');
    }
};
