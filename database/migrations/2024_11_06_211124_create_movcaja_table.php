<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('movcaja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conceptopago_id')->constrained('conceptopago')->onUpdate('restrict')->onDelete('restrict');
            $table->string('moneda', 255)->default('PEN');
            $table->decimal('tipocambio', 8, 3);
            $table->decimal('total', 8, 2);
            $table->foreignId('caja_id')->constrained('caja');
            $table->string('caja', 255);
            $table->bigInteger('turno_id')->constrained('turno');
            $table->json('json_turno');
            $table->foreignId('movimiento_id')->constrained('movimiento')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursal')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movcaja');
    }
};
