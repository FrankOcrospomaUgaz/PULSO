<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration {
    public function up()
    {
        Schema::table('huesped', function (Blueprint $table) {
            $table->foreignId('habitacion_id')
                ->nullable()
                ->after('movhabitacion_id')
                ->constrained('habitacion')
                ->onDelete('set null'); // Si la habitación se borra, dejarlo como null
        });

        // **Actualizar los registros actuales**
        DB::statement("
            UPDATE huesped h
            SET habitacion_id = (
                SELECT mh.habitacion_id
                FROM movhabitacion mh
                WHERE mh.id = h.movhabitacion_id
                LIMIT 1
            )
            WHERE h.habitacion_id IS NULL
        ");
    }

    public function down()
    {
        Schema::table('huesped', function (Blueprint $table) {
            $table->dropForeign(['habitacion_id']);
            $table->dropColumn('habitacion_id');
        });
    }
};