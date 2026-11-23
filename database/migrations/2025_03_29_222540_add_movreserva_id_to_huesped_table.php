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
        Schema::table('huesped', function (Blueprint $table) {
            // Hacer que 'movhabitacion_id' sea nullable
            $table->unsignedBigInteger('movhabitacion_id')->nullable()->change();

            // Agregar nueva columna 'movreserva_id' nullable con foreign key
            $table->foreignId('movreserva_id')
                ->nullable()
                ->constrained('movreserva')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('huesped', function (Blueprint $table) {
            //
        });
    }
};
