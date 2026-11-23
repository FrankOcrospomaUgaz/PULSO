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
        Schema::create('det_movventa', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_detalle')->default('DETALLADO')->comment('DETALLADO, GLOSA');
            $table->foreignId('movventa_id')->constrained('movventa')->onUpdate('cascade')->onDelete('cascade');
            $table->string('codigo');
            $table->string('descripcion');
            $table->foreignId('producto_id')->nullable()->constrained('producto');
            $table->json('json_producto')->nullable();
            $table->json('json_medidaproducto')->nullable();
            $table->foreignId('unidad_id')->constrained('unidad');
            $table->json('json_unidad');
            $table->foreignId('igv_id')->nullable()->constrained('igv');
            $table->json('json_igv')->nullable();
            $table->decimal('cantidad', 24, 6);
            $table->decimal('monto', 24, 6);
            $table->decimal('dcto_porcentaje', 24, 6);
            $table->decimal('monto_original', 24, 6);
            $table->text('comentario');
            $table->json('det_categoria');
            $table->json('opc_categoria');
            $table->foreignId('det_movventa_id')->nullable()->constrained('det_movventa');
            $table->json('complementos')->default(json_encode([]));
            $table->string('situacion', 1)->default('E');
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
        Schema::dropIfExists('det_movventa');
    }
};
