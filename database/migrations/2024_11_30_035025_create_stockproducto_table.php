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
        Schema::create('stockproducto', function (Blueprint $table) {
            $table->id(); // Crea la columna 'id' como clave primaria y auto incremental
            $table->decimal('stockbase', 24, 6); // Crea la columna 'stockbase' con tipo numeric(24,6)
            $table->foreignId('producto_id')->constrained('producto')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('unidadbase_id')->constrained('unidad')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursal')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps(); // Esto creará 'created_at' y 'updated_at'
            $table->softDeletes(); // Esto creará la columna 'deleted_at' para soft deletes
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stockproducto');
    }
};
