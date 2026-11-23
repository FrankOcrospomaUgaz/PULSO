<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('valordet_respuesta', function (Blueprint $table) {
            $table->id();
            $table->text('valor');
            $table->foreignId('det_respuesta_id')->constrained('det_respuesta')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursal')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('valordet_respuesta');
    }
};
