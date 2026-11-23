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
        Schema::create('billeteradigital', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion',255);
            $table->integer('orden');
            $table->boolean('situacion')->default(true); // Situación con valor por defecto 'E'
            $table->timestamps();
            $table->softDeletes(); // deleted_at para eliminaciones lógicas

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billeteradigital');
    }
};
