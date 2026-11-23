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
        Schema::table('habitacion', function (Blueprint $table) {
            $table->string('descripcion', 255)->nullable()->after('tipohabitacion_id');
        });

        // Rellenar las habitaciones existentes con la descripción de su tipohabitacion
        DB::statement("
            UPDATE habitacion
            SET descripcion = (
                SELECT tipohabitacion.descripcion
                FROM tipohabitacion
                WHERE tipohabitacion.id = habitacion.tipohabitacion_id
            )
        ");

        // Asegurar que el campo no permita valores nulos después de llenarlo
        Schema::table('habitacion', function (Blueprint $table) {
            $table->string('descripcion', 255)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('habitacion', function (Blueprint $table) {
            $table->dropColumn('descripcion');
        });
    }
};
