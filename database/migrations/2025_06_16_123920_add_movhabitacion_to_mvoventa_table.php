<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Agregar la columna movhabitacion_id en la tabla movventa
        Schema::table('movventa', function (Blueprint $table) {
            $table->foreignId('movhabitacion_id')
                ->nullable()
                ->constrained('movhabitacion')
                ->onDelete('set null'); // Si la venta se borra, dejarlo como null
        });

        // Ejecutar la sentencia SQL para actualizar movhabitacion_id
        DB::statement("
            UPDATE movventa
            SET movhabitacion_id = mh.id
            FROM movhabitacion mh
            WHERE movventa.movimiento_id = mh.movimiento_id
              AND movventa.movhabitacion_id IS NULL
              AND (mh.situacion = 'P' OR mh.situacion = 'C');
        ");
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // En este caso no es necesario revertir la sentencia SQL en el down, 
        // ya que estamos solo agregando una columna.
        Schema::table('movventa', function (Blueprint $table) {
            $table->dropForeign(['movhabitacion_id']);
            $table->dropColumn('movhabitacion_id');
        });
    }
};
