<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movcompra', function (Blueprint $table) {
            $table->id();
            $table->string('serie');
            $table->string('anio');
            $table->string('tipo_detalle')->default('DETALLADO')->comment('DETALLADO, GLOSA');
            $table->string('incluye_igv', 1)->default('N')->comment('S => El total incluye IGV, N => El total no incluye IGV');
            $table->string('tipo_pago')->default('CONTADO')->comment('CONTADO, CREDITO');
            $table->string('afecta_caja', 1)->default('N')->comment('S => Tiene movimiento de caja, N => Solo informativo');
            $table->string('moneda')->default('PEN');
            $table->decimal('tipocambio', 8, 3);
            $table->decimal('subtotal', 8, 2);
            $table->decimal('igv', 8, 2);
            $table->decimal('total', 8, 2)->comment('subtotal + igv');
            $table->string('afecta_kardex', 1)->default('S')->comment('S => Genera registro de kárdex, N => Solo informativo');
            $table->foreignId('movimiento_id')->constrained('movimiento')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursal')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movcompra');
    }
};
