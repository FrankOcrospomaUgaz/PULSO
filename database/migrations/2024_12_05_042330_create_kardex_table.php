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
        Schema::create('kardex', function (Blueprint $table) {
            $table->id(); // id BIGINT
            $table->foreignId('detalle_id')->nullable()->constrained('det_movventa'); // detalle_id BIGINT
            $table->foreignId('producto_id')->constrained('producto'); // producto_id BIGINT
            $table->json('json_producto'); // json_producto JSON
            $table->foreignId('unidad_id')->constrained('unidad'); // unidad_id BIGINT
            $table->json('json_listaunidad'); // json_listaunidad JSON
            $table->decimal('cantidad', 24, 6); // cantidad NUMERIC(24,6)
            $table->foreignId('unidadbase_id')->constrained('unidad'); // unidadbase_id BIGINT
            $table->decimal('cantidadbase', 24, 6); // cantidadbase NUMERIC(24,6)
            $table->decimal('preciounitario', 24, 6); // preciounitario NUMERIC(24,6)
            $table->string('moneda', 255)->default('PEN'); // moneda VARCHAR(255) DEFAULT 'PEN'
            $table->decimal('tipocambio', 8, 3); // tipocambio NUMERIC(8,3)
            $table->decimal('total', 24, 6); // total NUMERIC(24,6)
            $table->timestamp('fecha'); // fecha TIMESTAMP
            $table->string('situacion', 1)->default('E'); // situacion VARCHAR(1) DEFAULT 'E'
            $table->foreignId('usuario_id')->constrained('users'); // usuario_id BIGINT
            $table->string('usuario', 255); // usuario VARCHAR(255)
            $table->foreignId('movimiento_id'); // movimiento_id BIGINT
            $table->foreignId('tipomovimiento_id'); // tipomovimiento_id BIGINT
            $table->foreignId('tipodocumento_id'); // tipodocumento_id BIGINT
            $table->foreignId('sucursal_id'); // sucursal_id BIGINT

            $table->decimal('stockanterior', 24, 6)->default(0); // stockanterior NUMERIC(24,6) DEFAULT 0
            $table->decimal('stockactual', 24, 6)->default(0); // stockactual NUMERIC(24,6) DEFAULT 0
            $table->timestamps(); // created_at, updated_at
            $table->timestamp('deleted_at')->nullable(); // deleted_at TIMESTAMP

            // Constraints
            $table->foreign('movimiento_id')->references('id')->on('movimiento')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('sucursal_id')->references('id')->on('sucursal')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('tipodocumento_id')->references('id')->on('tipodocumento')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('tipomovimiento_id')->references('id')->on('tipomovimiento')->onDelete('restrict')->onUpdate('restrict');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kardex');
    }
};
