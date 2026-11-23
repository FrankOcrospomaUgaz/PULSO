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
        Schema::table('movreserva', function (Blueprint $table) {
            $table->foreignId('salon_id')
                ->nullable()
                ->constrained('salon')
                ->onDelete('set null');
        });
        
    }
    
    public function down(): void
    {
        Schema::table('movreserva', function (Blueprint $table) {
            $table->dropColumn('salon_id');
        });
    }
    
};
