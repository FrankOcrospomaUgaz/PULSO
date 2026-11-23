<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminamos el constraint anterior y agregamos el nuevo con 'transferencia'
        DB::statement("
            ALTER TABLE compromiso_pago
            DROP CONSTRAINT compromiso_pago_forma_pago_check,
            ADD CONSTRAINT compromiso_pago_forma_pago_check
            CHECK (forma_pago IN ('efectivo', 'tarjeta', 'deposito', 'billetera', 'transferencia'))
        ");
    }

    public function down(): void
    {
        // Revertimos al estado anterior (sin 'transferencia')
        DB::statement("
            ALTER TABLE compromiso_pago
            DROP CONSTRAINT compromiso_pago_forma_pago_check,
            ADD CONSTRAINT compromiso_pago_forma_pago_check
            CHECK (forma_pago IN ('efectivo', 'tarjeta', 'deposito', 'billetera'))
        ");
    }
};
