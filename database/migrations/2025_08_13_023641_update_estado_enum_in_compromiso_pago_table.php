<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // En PostgreSQL, Laravel crea el enum como CHECK sobre varchar,
        // por lo que actualizamos la restricción.
        DB::statement("
            ALTER TABLE compromiso_pago
            DROP CONSTRAINT compromiso_pago_estado_check,
            ADD CONSTRAINT compromiso_pago_estado_check
            CHECK (estado IN ('pendiente', 'pagado', 'vencido', 'anulado'))
        ");
    }

    public function down(): void
    {
        // Revertir a los valores anteriores
        DB::statement("
            ALTER TABLE compromiso_pago
            DROP CONSTRAINT compromiso_pago_estado_check,
            ADD CONSTRAINT compromiso_pago_estado_check
            CHECK (estado IN ('pendiente', 'pagado', 'vencido'))
        ");
    }
};
