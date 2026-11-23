<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variables_plantilla', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique()->comment('Código de la variable, ej: {{NOMBRE_CLIENTE}}');
            $table->boolean('situacion')->default(true)->comment('true=Activo, false=Inactivo');
            $table->timestamps();
            $table->softDeletes();
        });

        // Insertar variables detectadas en tu contrato
        DB::table('variables_plantilla')->insert([
            ['codigo' => '{{ nombre_arrendador }}', 'situacion' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => '{{ dni_arrendador }}', 'situacion' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => '{{ direccion_arrendador }}', 'situacion' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => '{{ nombre_arrendatario }}', 'situacion' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => '{{ dni_arrendatario }}', 'situacion' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => '{{ celular_arrendatario }}', 'situacion' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => '{{ direccion_departamento }}', 'situacion' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => '{{ ciudad_departamento }}', 'situacion' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => '{{ duracion_contrato }}', 'situacion' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => '{{ fecha_inicio }}', 'situacion' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => '{{ fecha_fin }}', 'situacion' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => '{{ dias_anticipacion }}', 'situacion' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => '{{ monto_alquiler }}', 'situacion' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => '{{ monto_alquiler_literal }}', 'situacion' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => '{{ dia_pago }}', 'situacion' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => '{{ monto_garantia }}', 'situacion' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => '{{ monto_garantia_literal }}', 'situacion' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => '{{ uso_inmueble }}', 'situacion' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => '{{ fecha_firma }}', 'situacion' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('variables_plantilla');
    }
};
