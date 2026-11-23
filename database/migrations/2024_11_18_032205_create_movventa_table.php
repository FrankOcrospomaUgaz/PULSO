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
        Schema::create('movventa', function (Blueprint $table) {
            $table->id();
            $table->json('json_sucursal');
            $table->string('serie');
            $table->string('anio');
            $table->string('tipo_detalle')->default('DETALLADO')->comment('DETALLADO, GLOSA');
            $table->string('consumo', 1)->default('N')->comment('S => Venta por consumo, N => Venta detallada');
            $table->string('tipo_pago')->default('CONTADO')->comment('CONTADO, CREDITO');
            $table->string('estado')->default('')->comment('Estado de la venta respecto a SUNAT, en caso sea factura y/o boleta');
            $table->string('tipo_venta')->default('MINORISTA')->comment('MINORISTA, MAYORISTA');
            $table->string('moneda')->default('PEN');
            $table->decimal('tipocambio', 8, 3);
            $table->decimal('subtotal', 8, 2);
            $table->decimal('igv', 8, 2);
            $table->decimal('total', 8, 2)->comment('subtotal + igv');
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
        Schema::dropIfExists('movventa');
    }
};
