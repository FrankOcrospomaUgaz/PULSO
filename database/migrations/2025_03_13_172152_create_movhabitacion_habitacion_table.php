<?php

use App\Models\Habitacion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up()
    {
        // Agregar la columna `modo` a `movhabitacion`
        if (!Schema::hasColumn('movhabitacion', 'modo')) {
            Schema::table('movhabitacion', function (Blueprint $table) {
                $table->char('modo', 1)->default('U')->comment('U = Único, G = Grupal')->after('situacion');
            });
        }

        // Crear la tabla `movhabitacion_habitacion` si no existe
        if (!Schema::hasTable('movhabitacion_habitacion')) {
            Schema::create('movhabitacion_habitacion', function (Blueprint $table) {
                $table->id();
                $table->foreignId('movhabitacion_id')->constrained('movhabitacion')->onDelete('cascade');
                $table->foreignId('habitacion_id')->constrained('habitacion')->onDelete('cascade');
                $table->boolean('principal')->default(false);
                $table->decimal('tarifa', 10, 2)->nullable();
                $table->json('json_habitacion')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Obtener todas las ocupaciones actuales de `movhabitacion`
        $ocupaciones = DB::table('movhabitacion')
            ->select('id', 'habitacion_id', 'movreserva_id', 'fechaingreso', 'sucursal_id', 'preciohabitacion', 'day_use')
            ->get();

        // Agrupar por `movreserva_id`, `fechaingreso` y `sucursal_id`
        $ocupacionesAgrupadas = [];

        foreach ($ocupaciones as $ocupacion) {
            $claveGrupo = "{$ocupacion->movreserva_id}-{$ocupacion->fechaingreso}-{$ocupacion->sucursal_id}";

            if (!isset($ocupacionesAgrupadas[$claveGrupo])) {
                $ocupacionesAgrupadas[$claveGrupo] = [];
            }

            $ocupacionesAgrupadas[$claveGrupo][] = $ocupacion;
        }

        // Insertar habitaciones en la nueva tabla y actualizar el `modo`
        foreach ($ocupacionesAgrupadas as $grupo) {
            $primerRegistro = true;
            $esGrupal = count($grupo) > 1; // Si hay más de una habitación, es grupal
            $habitacion = Habitacion::find( $ocupacion->habitacion_id);
            foreach ($grupo as $ocupacion) {
                DB::table('movhabitacion_habitacion')->insert([
                    'movhabitacion_id' => $ocupacion->id,
                    'habitacion_id' => $ocupacion->habitacion_id,
                    'principal' => $primerRegistro ? true : false, // Solo una principal
                    'tarifa' => $ocupacion->preciohabitacion,
                    'json_habitacion' => json_encode($habitacion), // Datos adicionales si aplica
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $primerRegistro = false; // Las siguientes habitaciones no serán principales
            }

            // Actualizar `modo` en `movhabitacion`
            DB::table('movhabitacion')
                ->where('id', $grupo[0]->id)
                ->update(['modo' => $esGrupal ? 'G' : 'U']);
        }
    }

    public function down()
    {
        Schema::table('movhabitacion', function (Blueprint $table) {
            if (Schema::hasColumn('movhabitacion', 'modo')) {
                $table->dropColumn('modo');
            }
        });

        Schema::dropIfExists('movhabitacion_habitacion');
    }
};