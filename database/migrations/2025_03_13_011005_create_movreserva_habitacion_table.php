<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        // Crear la tabla si aún no existe
        if (!Schema::hasTable('movreserva_habitacion')) {
            Schema::create('movreserva_habitacion', function (Blueprint $table) {
                $table->id();
                $table->foreignId('movreserva_id')->constrained('movreserva')->onDelete('cascade');
                $table->foreignId('habitacion_id')->constrained('habitacion')->onDelete('cascade');
                $table->boolean('principal')->default(false);
                $table->decimal('tarifa', 10, 2)->nullable();
                $table->json('json_habitacion')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Obtener todas las reservas actuales
        $reservas = DB::table('movreserva')
            ->select('id', 'habitacionprincipal_id', 'movimiento_id', 'fechainicio', 'sucursal_id', 'preciohabitacion', 'json_habitaciones')
            ->get();

        // Crear un array para agrupar reservas duplicadas
        $reservasAgrupadas = [];

        foreach ($reservas as $reserva) {
            $claveGrupo = "{$reserva->movimiento_id}-{$reserva->fechainicio}-{$reserva->sucursal_id}";

            if (!isset($reservasAgrupadas[$claveGrupo])) {
                $reservasAgrupadas[$claveGrupo] = [];
            }

            $reservasAgrupadas[$claveGrupo][] = $reserva;
        }

        // Insertar habitaciones en la nueva tabla
        foreach ($reservasAgrupadas as $grupo) {
            $primerRegistro = true;

            foreach ($grupo as $reserva) {
                DB::table('movreserva_habitacion')->insert([
                    'movreserva_id'  => $reserva->id,
                    'habitacion_id'  => $reserva->habitacionprincipal_id,
                    'principal'      => $primerRegistro ? true : false, // Solo una principal
                    'tarifa'         => $reserva->preciohabitacion,
                    'json_habitacion' => $reserva->json_habitaciones, // Si tiene más habitaciones
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                $primerRegistro = false; // Las siguientes habitaciones no serán principales
            }
        }
    }

    public function down()
    {
        Schema::table('movreserva_habitacion', function (Blueprint $table) {
            DB::table('movreserva_habitacion')->truncate(); // Elimina solo los datos sin borrar la tabla
        });
    }
};
