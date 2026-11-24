<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('paciente_clinico', 'acepta_notificaciones')) {
            Schema::table('paciente_clinico', function (Blueprint $table) {
                $table->boolean('acepta_notificaciones')->default(false);
                $table->timestamp('acepta_notificaciones_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('paciente_clinico', 'acepta_notificaciones')) {
            Schema::table('paciente_clinico', function (Blueprint $table) {
                $table->dropColumn(['acepta_notificaciones', 'acepta_notificaciones_at']);
            });
        }
    }
};
