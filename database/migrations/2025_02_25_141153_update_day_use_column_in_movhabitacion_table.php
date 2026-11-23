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
        // 1. Renombrar la columna antigua
        Schema::table('movhabitacion', function (Blueprint $table) {
            $table->renameColumn('day_use', 'old_day_use');
        });

        // 2. Crear la nueva columna JSON
        Schema::table('movhabitacion', function (Blueprint $table) {
            $table->json('day_use')->nullable();
        });

        // 3. Convertir los valores antiguos a JSON si existen
        DB::statement('UPDATE movhabitacion SET day_use = to_json(array[old_day_use]::text[]) WHERE old_day_use IS NOT NULL');

        // 4. Eliminar la columna antigua
        Schema::table('movhabitacion', function (Blueprint $table) {
            $table->dropColumn('old_day_use');
        });
    }

    public function down(): void
    {
        // 1. Crear nuevamente la columna decimal
        Schema::table('movhabitacion', function (Blueprint $table) {
            $table->decimal('old_day_use', 10, 2)->nullable();
        });

        // 2. Convertir los valores JSON a decimal (se tomará el primer valor del array)
        DB::statement("UPDATE movhabitacion SET old_day_use = (day_use->>0)::numeric WHERE day_use IS NOT NULL");

        // 3. Eliminar la columna JSON
        Schema::table('movhabitacion', function (Blueprint $table) {
            $table->dropColumn('day_use');
        });

        // 4. Renombrar la columna de nuevo a su nombre original
        Schema::table('movhabitacion', function (Blueprint $table) {
            $table->renameColumn('old_day_use', 'day_use');
        });
    }
    
};
