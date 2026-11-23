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
        Schema::create('sucursal', function (Blueprint $table) {
            $table->id(); // BIGINT AUTO_INCREMENT
            $table->string('razonsocial', 255); // VARCHAR(255), NOT NULL
            $table->string('nombrecomercial', 255); // VARCHAR(255), NOT NULL
            $table->string('direccion', 255); // VARCHAR(255), NOT NULL
            $table->string('ruc', 11); // VARCHAR(11), NOT NULL
            $table->string('email', 255); // VARCHAR(255), NOT NULL
            $table->string('telefonofijo', 10); // VARCHAR(10), NOT NULL
            $table->string('telefonomovil', 12); // VARCHAR(12), NOT NULL
            $table->text('logo'); // TEXT, NOT NULL
            $table->string('situacion', 1)->default('E'); // VARCHAR(1), NOT NULL, DEFAULT 'E'
            $table->string('facturacion', 1)->default('N'); // VARCHAR(1), NOT NULL, DEFAULT 'N'
            $table->string('tipo', 255)->default('VENTA'); // VARCHAR(255), NOT NULL, DEFAULT 'VENTA'
            $table->string('venta_mayorista', 1)->default('N'); // VARCHAR(1), NOT NULL, DEFAULT 'N'
            $table->integer('cantidad_precioventa')->default(0); // INTEGER, NOT NULL, DEFAULT 0
            $table->foreignId('empresa_id')->constrained('empresas')->onUpdate('cascade')->onDelete('cascade'); // Foreign Key
            $table->foreignId('ubigeo_id')->constrained('ubigeo')->onUpdate('restrict')->onDelete('restrict'); // Foreign Key
            $table->text('business_intelligence_url')->nullable(); // TEXT, NULLABLE
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at for soft deletes
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sucursal');
    }
};
