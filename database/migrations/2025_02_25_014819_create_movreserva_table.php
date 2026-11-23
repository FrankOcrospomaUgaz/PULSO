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
        Schema::create('movreserva', function (Blueprint $table) {
            $table->id();
            $table->string('observacion', 200)->nullable();
            $table->integer('nropersonas');
            $table->timestamp('fechavencimiento');
            $table->timestamp('fechainicio')->nullable();
            $table->timestamp('fechasalida')->nullable();
            $table->timestamp('fechacancelacion')->nullable();
            $table->integer('dias');
            $table->string('moneda', 255)->default('PEN');
            $table->decimal('tipocambio', 8, 3);
            $table->decimal('total', 8, 2);
            $table->foreignId('habitacionprincipal_id')->nullable()->constrained('habitacion')->onDelete('set null');
            $table->string('habitacion_principal', 255);
            $table->json('json_habitaciones')->nullable();
            $table->string('estado', 255)->default('FINALIZADO');
            $table->foreignId('movimiento_id')->constrained('movimiento')->onDelete('cascade');
            $table->foreignId('motivo_id')->constrained('motivoreserva')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursal')->onDelete('cascade');
            $table->string('celular', 12)->default('')->nullable();
            $table->string('email', 255)->default('')->nullable();
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movreserva');
    }
};
