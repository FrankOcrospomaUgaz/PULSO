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
           // Eliminar la clave foránea si existe y luego la columna reserva_id en movhabitacion
           Schema::table('movhabitacion', function (Blueprint $table) {
            if (Schema::hasColumn('movhabitacion', 'reserva_id')) {
                $table->dropForeign(['reserva_id']);
                $table->dropColumn('reserva_id');
            }
        });

        // Eliminar la tabla reserva si existe
        Schema::dropIfExists('reserva');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reserva', function (Blueprint $table) {
            //
        });
    }
};
