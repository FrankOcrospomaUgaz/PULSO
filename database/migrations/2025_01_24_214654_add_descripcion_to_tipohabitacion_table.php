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
          Schema::table('tipohabitacion', function (Blueprint $table) {
              $table->string('descripcion')->nullable()->default('Cama 1.5 Plz + TV + Baño');
          });
      }
  
      /**
       * Revierte la migración.
       *
       * @return void
       */
      public function down()
      {
          Schema::table('tipohabitacion', function (Blueprint $table) {
              $table->dropColumn('descripcion');
          });
      }
};
