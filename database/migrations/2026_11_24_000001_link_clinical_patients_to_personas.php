<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('paciente_clinico') && !Schema::hasColumn('paciente_clinico', 'persona_id')) {
            Schema::table('paciente_clinico', function (Blueprint $table) {
                $table->foreignId('persona_id')->nullable()->after('sucursal_id')->constrained('persona')->nullOnDelete();
                $table->unique('persona_id', 'paciente_clinico_persona_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('paciente_clinico') && Schema::hasColumn('paciente_clinico', 'persona_id')) {
            Schema::table('paciente_clinico', function (Blueprint $table) {
                $table->dropUnique('paciente_clinico_persona_unique');
                $table->dropConstrainedForeignId('persona_id');
            });
        }
    }
};
