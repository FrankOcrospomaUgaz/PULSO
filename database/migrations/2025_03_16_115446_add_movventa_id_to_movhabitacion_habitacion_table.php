<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::table('movhabitacion_habitacion', function (Blueprint $table) {
            $table->foreignId('movventa_id')
                ->nullable()
                ->after('movhabitacion_id')
                ->constrained('movventa')
                ->onDelete('set null'); // Si la venta se borra, dejarlo como null
        });

        // **Actualizar los registros actuales**
        DB::statement("
            UPDATE movhabitacion_habitacion mhh
            SET movventa_id = (
                SELECT mv.id
                FROM movhabitacion mh
                JOIN movventa mv ON mh.movimiento_id = mv.movimiento_id
                WHERE mh.id = mhh.movhabitacion_id
                LIMIT 1
            )
            WHERE mhh.movventa_id IS NULL
        ");
    }

    public function down()
    {
        Schema::table('movhabitacion_habitacion', function (Blueprint $table) {
            $table->dropForeign(['movventa_id']);
            $table->dropColumn('movventa_id');
        });
    }
};
