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
       Schema::create('ambiente', function (Blueprint $table) {
    $table->id();
    $table->string('numero')->comment('Número o identificador del ambiente');
    $table->enum('tipo', ['habitacion', 'stand'])->comment('Tipo de ambiente');
    $table->decimal('monto', 10, 2)->comment('Monto mensual del alquiler');
    $table->decimal('garantia', 10, 2)->comment('Monto de la garantía');
    $table->foreignId('piso_id')->constrained('piso')->onDelete('cascade');
    $table->foreignId('edificio_id')->constrained('edificio')->onDelete('cascade');
    $table->foreignId('sucursal_id')->constrained('sucursal')->onDelete('cascade');

    $table->timestamps();
    $table->softDeletes();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
