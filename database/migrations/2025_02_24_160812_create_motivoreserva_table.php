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
        Schema::create('motivoreserva', function (Blueprint $table) {
            $table->id();
            $table->string('codigo',255);
            $table->string('descripcion',255);
            $table->integer('orden');
            $table->boolean('situacion')->default(true); 
            $table->timestamps();
            $table->softDeletes(); // deleted_at para eliminaciones lógicas

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motivoreserva');
    }
};
