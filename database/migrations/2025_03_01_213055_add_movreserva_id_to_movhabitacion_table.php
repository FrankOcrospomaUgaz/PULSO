<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('movhabitacion', function (Blueprint $table) {
            // Primero agregamos la columna permitiendo valores nulos
            $table->foreignId('movreserva_id')->nullable()->constrained('movreserva')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movhabitacion', function (Blueprint $table) {
            $table->dropForeign(['movreserva_id']); // Eliminar la clave foránea
            $table->dropColumn('movreserva_id'); // Eliminar la columna
        });
    }
};
