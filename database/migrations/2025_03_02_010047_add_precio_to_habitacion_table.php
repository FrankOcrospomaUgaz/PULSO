<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Agregar la columna `precio` a la tabla `habitacion`
        Schema::table('habitacion', function (Blueprint $table) {
            $table->decimal('precio', 8, 2)->nullable()->after('tipohabitacion_id');
        });

        // Rellenar las habitaciones existentes con el precio de su tipohabitacion
        DB::statement("
            UPDATE habitacion
            SET precio = (
                SELECT tipohabitacion.precio
                FROM tipohabitacion
                WHERE tipohabitacion.id = habitacion.tipohabitacion_id
            )
        ");

        // Asegurar que el campo no permita valores nulos después de llenarlo
        Schema::table('habitacion', function (Blueprint $table) {
            $table->decimal('precio', 8, 2)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('habitacion', function (Blueprint $table) {
            $table->dropColumn('precio');
        });
    }
};
