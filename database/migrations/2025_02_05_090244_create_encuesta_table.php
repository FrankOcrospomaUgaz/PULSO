<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up() {
        // Habilitar la extensión uuid-ossp en PostgreSQL
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp";');

        Schema::create('encuesta', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->default(DB::raw('uuid_generate_v4()'));
            $table->string('nombre', 255);
            $table->text('descripcion');
            $table->timestamp('fecha');
            $table->timestamp('fechavencimiento')->nullable();
            $table->text('mensaje_finalizacion');
            $table->char('imprimible', 1)->default('N');
            $table->string('estado', 255)->default('VIGENTE');
            $table->char('situacion', 1)->default('E');
            $table->unsignedBigInteger('sucursal_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sucursal_id')->references('id')->on('sucursal')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    public function down() {
        Schema::dropIfExists('encuesta');
    }
};
