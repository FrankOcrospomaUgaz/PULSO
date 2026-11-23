<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('det_movcompra', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_detalle')->default('DETALLADO')->comment('DETALLADO, GLOSA');
            $table->foreignId('movcompra_id')->constrained('movcompra')->onUpdate('cascade')->onDelete('cascade');
            $table->string('codigo');
            $table->text('descripcion');
            $table->foreignId('producto_id')->nullable()->constrained('producto')->nullOnDelete();
            $table->json('json_producto')->nullable();
            $table->foreignId('unidad_id')->constrained('unidad')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('igv_id')->nullable()->constrained('igv')->nullOnDelete();
            $table->decimal('cantidad', 24, 6);
            $table->decimal('monto', 24, 6);
            $table->text('comentario');
            $table->string('situacion', 1)->default('E');
            $table->foreignId('sucursal_id')->constrained('sucursal')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('det_movcompra');
    }
};
