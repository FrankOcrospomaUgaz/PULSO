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
        Schema::create('housekeeping_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jornada_id')->constrained('housekeeping')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('habitacion_id')->constrained('habitacion')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('inicio_limpieza')->nullable();
            $table->timestamp('fin_limpieza')->nullable();
            $table->decimal('tiempo', 8, 2)->nullable()->comment('Tiempo en minutos');
            $table->string('estado', 50)->default('Pendiente')->comment('Pendiente, En progreso, Completado');
            $table->text('observaciones')->nullable();
            $table->foreignId('empleado_id')->constrained('persona')->onDelete('cascade')->onUpdate('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housekeeping_detalle');
    }
};
