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
        Schema::table('movhabitacion_habitacion', function (Blueprint $table) {
            $table->boolean('notificado_checkout_10')->default(false)->after('fechacheckout');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movhabitacion_habitacion', function (Blueprint $table) {
            $table->dropColumn('notificado_checkout_10');
        });
    }
};
