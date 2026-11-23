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
        Schema::create('movreserva_paquete', function (Blueprint $table) {
            $table->id();
            $table->string('observacion', 200)->nullable();
            $table->integer('nropersonas');
            $table->timestamp('fechavencimiento');
            $table->timestamp('fechainicio')->nullable();
            $table->timestamp('fechasalida')->nullable();
            $table->timestamp('fechacancelacion')->nullable();
            $table->decimal('precio_paquete', 8, 2);
            $table->decimal('total', 8, 2);
            $table->foreignId('salon_package_id')->nullable()->constrained('salon_packages')->onDelete('set null');
            $table->string('salon_package_name', 255);
            $table->json('json_paquetes')->nullable();
            $table->string('estado', 255)->default('PENDIENTE');
            $table->foreignId('movimiento_id')->constrained('movimiento')->onDelete('cascade');
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
        Schema::dropIfExists('movreserva_paquete');
    }
};
