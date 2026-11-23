<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tipoambiente', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique(); // Ej: habitacion, stand, oficina
            $table->string('descripcion')->nullable(); // Ej: "Habitación", "Stand para comercio", etc.
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipoambiente');
    }
};
