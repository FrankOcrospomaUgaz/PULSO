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
        Schema::table('movcaja', function (Blueprint $table) {
            $table->unsignedBigInteger('adelantomovreserva_id')->nullable()->after('movimiento_id');

            $table->foreign('adelantomovreserva_id')
                  ->references('id')
                  ->on('movreserva')
                  ->nullOnDelete(); // Si se elimina movreserva, dejar null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movcaja', function (Blueprint $table) {
            $table->dropForeign(['adelantomovreserva_id']);
            $table->dropColumn('adelantomovreserva_id');
        });
    }
};
