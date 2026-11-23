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
            if (!Schema::hasColumn('movventa', 'detraccion')) {
                $table->string('detraccion', 255)->default('N');
            }
            if (!Schema::hasColumn('movventa', 'porcentaje_detraccion')) {
                $table->decimal('porcentaje_detraccion', 24, 6)->default(0);
            }
            if (!Schema::hasColumn('movventa', 'cuenta_detraccion')) {
                $table->string('cuenta_detraccion', 255)->default('');
            }
            if (!Schema::hasColumn('movventa', 'codigo_bien_detraccion')) {
                $table->string('codigo_bien_detraccion', 255)->default('');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movventa', function (Blueprint $table) {
            $table->dropColumn([
                'detraccion',
                'porcentaje_detraccion',
                'cuenta_detraccion',
                'codigo_bien_detraccion',
            ]);
        });
    }
};
