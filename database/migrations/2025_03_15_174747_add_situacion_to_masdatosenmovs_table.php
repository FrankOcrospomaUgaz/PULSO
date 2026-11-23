<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        // Agregar nuevas columnas a `movreserva_habitacion`
        if (!Schema::hasColumn('movreserva_habitacion', 'fechacheckin')) {
            Schema::table('movreserva_habitacion', function (Blueprint $table) {
                $table->timestamp('fechacheckin')->nullable()->after('tarifa');
                $table->timestamp('fechacheckout')->nullable()->after('fechacheckin');
                $table->decimal('dias', 8, 2)->nullable()->after('fechacheckout');
                $table->decimal('total', 10, 2)->nullable()->after('dias');
            });
        }

        // Actualizar datos de `movreserva_habitacion`
        $reservas = DB::table('movreserva')
            ->select('id', 'habitacionprincipal_id', 'movimiento_id', 'fechainicio', 'fechasalida', 'sucursal_id', 'preciohabitacion', 'json_habitaciones')
            ->get();

        foreach ($reservas as $reserva) {
            DB::table('movreserva_habitacion')
                ->where('movreserva_id', $reserva->id)
                ->update([
                    'fechacheckin'  => $reserva->fechainicio,
                    'fechacheckout' => $reserva->fechasalida,
                    'dias'          => DB::raw("COALESCE(EXTRACT(EPOCH FROM (fechacheckout - fechacheckin)) / 86400.0, 1)"),
                    'total'         => $reserva->preciohabitacion,
                ]);
        }

        // Agregar nuevas columnas a `movhabitacion_habitacion`
        if (!Schema::hasColumn('movhabitacion_habitacion', 'fechacheckin')) {
            Schema::table('movhabitacion_habitacion', function (Blueprint $table) {
                $table->timestamp('fechacheckin')->nullable()->after('tarifa');
                $table->timestamp('fechacheckout')->nullable()->after('fechacheckin');
                $table->decimal('dias', 8, 2)->nullable()->after('fechacheckout');
                $table->decimal('horas', 8, 2)->nullable()->after('dias');
                $table->char('modalidad', 1)->default('D')->comment('D = Días, H = Horas')->after('horas');
                $table->decimal('total', 10, 2)->nullable()->after('modalidad');
            });
        }

        // Actualizar datos de `movhabitacion_habitacion`
        $ocupaciones = DB::table('movhabitacion')
            ->select('id', 'habitacion_id', 'movreserva_id', 'fechaingreso', 'fechasalida', 'sucursal_id', 'preciohabitacion', 'modo', 'horas', 'dias')
            ->get();

        foreach ($ocupaciones as $ocupacion) {
            DB::table('movhabitacion_habitacion')
                ->where('movhabitacion_id', $ocupacion->id)
                ->update([
                    'fechacheckin'  => $ocupacion->fechaingreso,
                    'fechacheckout' => $ocupacion->fechasalida,
                    'dias'          => $ocupacion->modo === 'D' ? ($ocupacion->dias ?? 1) : null,
                    'horas'         => $ocupacion->modo === 'H' ? ($ocupacion->horas ?? 1) : null,
                    'modalidad'     => $ocupacion->modo,
                    'total'         => $ocupacion->preciohabitacion,
                ]);
        }
    }

    public function down()
    {
        Schema::table('movreserva_habitacion', function (Blueprint $table) {
            $table->dropColumn(['fechacheckin', 'fechacheckout', 'dias', 'total']);
        });

        Schema::table('movhabitacion_habitacion', function (Blueprint $table) {
            $table->dropColumn(['fechacheckin', 'fechacheckout', 'dias', 'horas', 'modalidad', 'total']);
        });
    }
};
