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
        Schema::table('persona', function (Blueprint $table) {
            $table->boolean('no_grato')->default(false)->after('compartido'); // Puedes cambiar "after" si lo deseas
        });
    }
    
    public function down(): void
    {
        Schema::table('persona', function (Blueprint $table) {
            $table->dropColumn('no_grato');
        });
    }
    
};
