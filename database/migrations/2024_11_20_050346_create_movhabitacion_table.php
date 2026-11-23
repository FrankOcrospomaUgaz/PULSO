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
        Schema::create('movhabitacion', function (Blueprint $table) {
            $table->id();
            $table->decimal('descuento', 10, 2)->nullable();
            $table->timestamp('fechaingreso');
            $table->timestamp('fechasalida')->nullable();
            $table->decimal('dias', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->decimal('preciohabitacion', 10, 4)->nullable();
            $table->string('situacion', 1)->default('A');
            $table->string('comentario', 255)->nullable();
            $table->string('moneda')->default('PEN');
            $table->unsignedBigInteger('habitacion_id')->constrained('habitacion')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('reserva_id')->nullable();
            $table->unsignedBigInteger('movimiento_id')->constrained('movimiento')->onUpdate('cascade')->onDelete('cascade');
            $table->decimal('early_checkin', 10, 2)->nullable();
            $table->decimal('late_checkout', 10, 2)->nullable();
            $table->decimal('day_use', 10, 2)->nullable();
            $table->foreignId('sucursal_id')->constrained('sucursal')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            // Foreign keys
            $table->foreign('reserva_id')->references('id')->on('reserva')->onDelete('cascade')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movhabitacion');
    }
};
