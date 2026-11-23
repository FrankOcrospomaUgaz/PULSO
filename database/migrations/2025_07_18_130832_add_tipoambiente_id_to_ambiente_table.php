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
        Schema::table('ambiente', function (Blueprint $table) {
            // Eliminar la columna 'tipo' si existe
            if (Schema::hasColumn('ambiente', 'tipo')) {
                $table->dropColumn('tipo');
            }

            // Agregar la columna 'tipoambiente_id' como foránea
            $table->foreignId('tipoambiente_id')
                  ->nullable()
                  ->constrained('tipoambiente')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ambiente', function (Blueprint $table) {
            // Eliminar la foránea
            $table->dropForeign(['tipoambiente_id']);
            $table->dropColumn('tipoambiente_id');

            // Restaurar la columna 'tipo' como enum (opcional)
            $table->enum('tipo', ['habitacion', 'stand'])->nullable();
        });
    }
};
