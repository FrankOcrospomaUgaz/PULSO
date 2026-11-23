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
       Schema::table('movventa', function (Blueprint $table) {
            $table->foreignId('compromiso_pago_id')
                ->nullable()
                ->constrained('compromiso_pago')
                ->onDelete('set null'); // Si la venta se borra, dejarlo como null
        });
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
