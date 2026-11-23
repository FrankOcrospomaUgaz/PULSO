<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('origenreserva', function (Blueprint $table) {
            $table->longText('logo')->nullable()->after('color'); // Almacena la imagen en Base64
        });
    }

    public function down() {
        Schema::table('origenreserva', function (Blueprint $table) {
            $table->dropColumn('logo');
        });
    }
};
