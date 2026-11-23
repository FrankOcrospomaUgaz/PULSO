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
        Schema::create('housekeeping', function (Blueprint $table) {
            $table->id();
            $table->timestamp('fecha_programada')->nullable();
            $table->decimal('tiempo_total', 8, 2)->nullable()->comment('Tiempo en minutos');
            $table->string('estado', 50)->default('Pendiente')->comment('En progreso, Completado, Pendiente');
            $table->text('observaciones')->nullable();
            $table->foreignId('sucursal_id')->constrained('sucursal')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housekeeping');
    }
};
