<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('movventa', function (Blueprint $table) {
            $table->foreignId('movreserva_id')
                ->nullable()
                ->constrained('movreserva')
                ->onDelete('set null'); // Si la venta se borra, dejarlo como null
        });
             // Ejecutar la sentencia SQL para actualizar movreserva_id
        DB::statement("
            UPDATE movventa mv
            SET movreserva_id = mr.id
            FROM movreserva mr
            WHERE mv.movimiento_id = mr.movimiento_id
              AND mv.movreserva_id IS NULL;
        ");
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movventa', function (Blueprint $table) {
            //
        });
    }
};
