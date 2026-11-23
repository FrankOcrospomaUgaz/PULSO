<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('movreserva', function (Blueprint $table) {
            $table->char('modo', 1)->default('U')->comment('U = Único, G = Grupal')->after('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('movreserva', function (Blueprint $table) {
            $table->dropColumn('modo');
        });
    }
};
