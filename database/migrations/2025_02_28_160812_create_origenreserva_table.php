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
        Schema::create('origenreserva', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion',255);
            $table->string('color',255);

            $table->boolean('situacion')->default(true); 
            $table->foreignId('sucursal_id')->constrained('sucursal')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes(); // deleted_at para eliminaciones lógicas

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('origenreserva');
    }
};
