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
    public function up()
    {
        Schema::table('habitacion', function (Blueprint $table) {
            // Agregar la columna 'sucursal_id' con un valor por defecto temporalmente
            $table->unsignedBigInteger('sucursal_id')->default(39)->after('id');
        });

        // Actualizar los registros existentes para asignar el valor por defecto
        DB::table('habitacion')->update(['sucursal_id' => 39]);

        // Modificar la columna para eliminar el valor por defecto y agregar la clave foránea
        Schema::table('habitacion', function (Blueprint $table) {
            $table->foreign('sucursal_id')
                ->references('id')
                ->on('sucursal')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('habitacion', function (Blueprint $table) {
            // Primero eliminamos la clave foránea
            $table->dropForeign(['sucursal_id']);

            // Luego eliminamos la columna 'sucursal_id'
            $table->dropColumn('sucursal_id');
        });
    }
};
