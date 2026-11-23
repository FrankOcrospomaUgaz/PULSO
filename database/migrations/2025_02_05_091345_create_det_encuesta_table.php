<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('det_encuesta', function (Blueprint $table) {
            $table->id();
            $table->text('enunciado');
            $table->integer('orden')->default(0);
            $table->char('obligatorio', 1)->default('N')->comment('S => Sí, N => No');
            $table->json('opciones');
            $table->char('situacion', 1)->default('E');
            $table->foreignId('categoriapregunta_id')->constrained('categoriapregunta')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('categoriapregunta', 255);
            $table->foreignId('tipopregunta_id')->constrained('tipopregunta')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('encuesta_id')->constrained('encuesta')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursal')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('det_encuesta');
    }
};

