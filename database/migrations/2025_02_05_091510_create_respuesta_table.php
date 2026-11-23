<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('respuesta', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 255);
            $table->timestamp('fecha');
            $table->foreignId('persona_id');
            $table->string('persona', 255);
            $table->foreignId('encuesta_id')->constrained('encuesta')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('movimiento_id')->nullable()->constrained('movimiento')->cascadeOnUpdate()->nullOnDelete();
            $table->json('movimiento')->nullable();
            $table->foreignId('sucursal_id')->constrained('sucursal')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('respuesta');
    }
};

