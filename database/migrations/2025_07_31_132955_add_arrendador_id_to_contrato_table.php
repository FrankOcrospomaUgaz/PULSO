<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddArrendadorIdToContratoTable extends Migration
{
    public function up()
    {
        // Eliminar todos los contratos existentes para evitar conflictos
        DB::table('contrato')->delete();

        Schema::table('contrato', function (Blueprint $table) {
            $table->unsignedBigInteger('arrendador_id')->after('persona_id');
            $table->foreign('arrendador_id')->references('id')->on('persona')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::table('contrato', function (Blueprint $table) {
            $table->dropForeign(['arrendador_id']);
            $table->dropColumn('arrendador_id');
        });
    }
}
