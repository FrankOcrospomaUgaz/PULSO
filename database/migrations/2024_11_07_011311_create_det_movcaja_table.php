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
        Schema::create('det_movcaja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movcaja_id')->constrained('movcaja')->onUpdate('cascade')->onDelete('cascade');
            $table->string('tipo', 255)->comment('PAGADO, DEUDA');
            $table->timestamp('fechavencimiento')->nullable()->comment('Cancelar en la fecha');
            $table->timestamp('fechapago')->nullable()->comment('Pagado en la fecha');
            $table->foreignId('mediopago_id')->constrained('mediopago')->onUpdate('restrict')->onDelete('restrict');
            $table->string('mediopago', 255);
            $table->string('numero', 255);
            $table->foreignId('tarjeta_id')->nullable()->constrained('tarjeta')->onUpdate('restrict')->onDelete('restrict');
            $table->string('tarjeta', 255);
            $table->foreignId('banco_id')->nullable()->constrained('banco')->onUpdate('restrict')->onDelete('restrict');
            $table->string('banco', 255);
            $table->foreignId('billeteradigital_id')->nullable()->constrained('billeteradigital')->onUpdate('restrict')->onDelete('restrict');
            $table->string('billeteradigital', 255);
            $table->foreignId('pos_id')->nullable()->constrained('pasarelapago')->onUpdate('restrict')->onDelete('restrict');
            $table->string('pos', 255);
            $table->decimal('monto', 24, 6);
            $table->text('comentario');
            $table->string('situacion', 1)->default('A');
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
        Schema::dropIfExists('det_movcaja');
    }
};
