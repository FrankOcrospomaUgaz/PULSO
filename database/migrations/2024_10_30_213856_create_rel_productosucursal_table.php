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
        Schema::create('rel_productosucursal', function (Blueprint $table) {
            $table->id();
            $table->string('situacion', 1)->default('E');
            $table->date('fechavencimiento')->nullable();
            $table->decimal('stockminimo', 24, 6);
            $table->decimal('stockmaximo', 24, 6);
            $table->decimal('stockoptimo', 24, 6);
            $table->decimal('minimovender', 24, 6);
            $table->decimal('minimocomprar', 24, 6);
            $table->unsignedBigInteger('ubicacion_id')->nullable();
            $table->integer('fila')->default(0);
            $table->integer('columna')->default(0);
            $table->string('favorito', 1)->default('N');
            $table->unsignedBigInteger('subalmacen_id')->nullable();
            $table->unsignedBigInteger('igv_id')->nullable();
            $table->string('venta_unitaria', 255)->default('N')->comment('S => Sí, N => No');
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->timestamps();
            $table->softDeletes();
            $table->decimal('duracion', 8, 2)->default(0)->comment('minutos');
            $table->unsignedBigInteger('proveedor_id')->nullable();

            // Definición de claves foráneas
            $table->foreign('igv_id')->references('id')->on('igv')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('producto_id')->references('id')->on('producto')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('proveedor_id')->references('id')->on('persona')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('subalmacen_id')->references('id')->on('subalmacen')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('sucursal_id')->references('id')->on('sucursal')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('ubicacion_id')->references('id')->on('ubicacion')->onUpdate('cascade')->onDelete('set null');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rel_productosucursal');
    }
};
