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
        // Agregar campos
        Schema::table('movhabitacion_habitacion', function (Blueprint $table) {
            $table->decimal('early_checkin', 10, 2)->nullable();
            $table->decimal('late_checkout', 10, 2)->nullable();
        });

        Schema::table('movreserva_habitacion', function (Blueprint $table) {
            $table->decimal('early_checkin', 10, 2)->nullable();
            $table->decimal('late_checkout', 10, 2)->nullable();
        });

        // 🔁 Actualizar datos en movhabitacion_habitacion
        DB::statement("
            UPDATE movhabitacion_habitacion mhh
            SET early_checkin = mh.early_checkin,
                late_checkout = mh.late_checkout
            FROM movhabitacion mh
            WHERE mhh.movhabitacion_id = mh.id
        ");

        // 🔁 Actualizar datos en movreserva_habitacion
        DB::statement("
            UPDATE movreserva_habitacion mrh
            SET early_checkin = mh.early_checkin,
                late_checkout = mh.late_checkout
            FROM movhabitacion mh
            INNER JOIN movreserva mr ON mh.movreserva_id = mr.id
            WHERE mrh.movreserva_id = mr.id
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movhabitacion_habitacion', function (Blueprint $table) {
            $table->dropColumn(['early_checkin', 'late_checkout']);
        });

        Schema::table('movreserva_habitacion', function (Blueprint $table) {
            $table->dropColumn(['early_checkin', 'late_checkout']);
        });
    }
};
