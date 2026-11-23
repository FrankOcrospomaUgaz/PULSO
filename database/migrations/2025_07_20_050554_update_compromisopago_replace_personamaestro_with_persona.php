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
        Schema::table('compromiso_pago', function (Blueprint $table) {
            // Eliminar la relación existente si existe
            if (Schema::hasColumn('compromiso_pago', 'personamaestro_id')) {
                $table->dropForeign(['personamaestro_id']);
                $table->dropColumn('personamaestro_id');
            }

            // Agregar la nueva relación con persona
            $table->foreignId('persona_id')
                ->nullable()
                ->constrained('persona')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compromiso_pago', function (Blueprint $table) {
            // Eliminar la nueva relación
            $table->dropForeign(['persona_id']);
            $table->dropColumn('persona_id');

            // Restaurar la relación con personamaestro
            $table->foreignId('personamaestro_id')
                ->nullable()
                ->constrained('personamaestro')
                ->onDelete('restrict');
        });
    }
};
