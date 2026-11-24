<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('horario_profesional_clinico')) {
            Schema::create('horario_profesional_clinico', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sucursal_id')->constrained('sucursal')->cascadeOnDelete();
                $table->foreignId('profesional_id')->constrained('profesional_clinico')->cascadeOnDelete();
                $table->unsignedSmallInteger('dia_semana');
                $table->time('hora_inicio');
                $table->time('hora_fin');
                $table->boolean('activo')->default(true);
                $table->timestamps();
                $table->index(['sucursal_id', 'profesional_id', 'dia_semana', 'activo'], 'horario_clinico_busqueda_idx');
            });
        }

        if (!Schema::hasTable('notificacion_clinica')) {
            Schema::create('notificacion_clinica', function (Blueprint $table) {
                $table->id();
                $table->string('clave', 100)->unique();
                $table->foreignId('sucursal_id')->constrained('sucursal')->cascadeOnDelete();
                $table->foreignId('paciente_id')->nullable()->constrained('paciente_clinico')->nullOnDelete();
                $table->foreignId('cita_id')->nullable()->constrained('cita_clinica')->nullOnDelete();
                $table->string('tipo', 30);
                $table->date('fecha_evento');
                $table->string('telefono', 40);
                $table->string('estado', 20)->default('PENDIENTE');
                $table->json('respuesta')->nullable();
                $table->text('error')->nullable();
                $table->timestamp('enviado_at')->nullable();
                $table->timestamps();
                $table->index(['sucursal_id', 'tipo', 'fecha_evento', 'estado'], 'notificacion_clinica_estado_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacion_clinica');
        Schema::dropIfExists('horario_profesional_clinico');
    }
};
