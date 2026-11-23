<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('movventa', function (Blueprint $table) {
            $table->char('tipo_cobro', 1)
                  ->nullable()
                  ->default('N')
                  ->comment('N: Normal, L: Late Checkout, E: Early Checkin')
                  ->after('estado'); // Ajusta la posición si quieres ubicarlo en otra parte
        });

        // Asignar 'N' a todos los registros existentes
        DB::table('movventa')->update(['tipo_cobro' => 'N']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movventa', function (Blueprint $table) {
            $table->dropColumn('tipo_cobro');
        });
    }
};
