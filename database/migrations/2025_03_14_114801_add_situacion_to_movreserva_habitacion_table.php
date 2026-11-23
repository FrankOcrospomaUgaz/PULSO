<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        // Agregar el campo situacion a movhabitacion_habitacion (CHAR 1)
        Schema::table('movhabitacion_habitacion', function (Blueprint $table) {
            $table->char('situacion', 1)->default('A')->comment('A = Activo, P = Pagado, C = Cancelado')->after('json_habitacion');
        });

        // Actualizar la data existente en movhabitacion_habitacion según movhabitacion
        DB::statement("
            UPDATE movhabitacion_habitacion AS mhh
            SET situacion = (
                SELECT mh.situacion
                FROM movhabitacion mh
                WHERE mh.id = mhh.movhabitacion_id
            )
        ");

        // Agregar el campo situacion a movreserva_habitacion (VARCHAR 255)
        Schema::table('movreserva_habitacion', function (Blueprint $table) {
            $table->string('situacion', 255)->default('PENDIENTE')->comment('PENDIENTE, EN ATENCIÓN, FINALIZADO')->after('json_habitacion');
        });

        // Actualizar la data existente en movreserva_habitacion según movreserva
        DB::statement("
            UPDATE movreserva_habitacion AS mrh
            SET situacion = (
                SELECT mr.estado
                FROM movreserva mr
                WHERE mr.id = mrh.movreserva_id
            )
        ");
    }

    public function down()
    {
        Schema::table('movhabitacion_habitacion', function (Blueprint $table) {
            $table->dropColumn('situacion');
        });

        Schema::table('movreserva_habitacion', function (Blueprint $table) {
            $table->dropColumn('situacion');
        });
    }
};
