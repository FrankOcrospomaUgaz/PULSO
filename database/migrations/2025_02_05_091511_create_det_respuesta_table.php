<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('det_respuesta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('det_encuesta_id')->constrained('det_encuesta')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('respuesta_id')->constrained('respuesta')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursal')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('det_respuesta');
    }
};
