<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSituacionToContratoTable extends Migration
{
    public function up()
    {
        Schema::table('contrato', function (Blueprint $table) {
            $table->char('situacion', 1)
                ->default('A')
                ->comment("Situación del contrato: A=Activo, V=Vencido, R=Resuelto, C=Cancelado, S=Suspensión temporal");
        });
    }

    public function down()
    {
        Schema::table('contrato', function (Blueprint $table) {
            $table->dropColumn('situacion');
        });
    }
}
