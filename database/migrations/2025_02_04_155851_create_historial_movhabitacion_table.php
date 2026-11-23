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
        Schema::create('historial_habitacion', function (Blueprint $table) {
            $table->id();
            $table->string('situacion_pago', 1)->default('N');
            $table->string('situacion_checkout', 1)->default('N');
            $table->foreignId('movhabitacion_id')->constrained('movhabitacion')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('relturnocaja_id')->constrained('rel_turnocaja')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursal')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_movhabitacion');
    }
};
