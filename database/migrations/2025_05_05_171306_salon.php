<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('salon', function (Blueprint $table) {
            $table->id();
            $table->string('titulo'); // Ej: Nuestro salón de recepciones
            $table->text('descripcion_corta');
            $table->text('descripcion_larga');
            $table->text('imagen')->nullable(); // Puede ser clase de ícono o URL
            $table->char('situacion', 1)->default('A'); // A: Activo, D: Desactivado
            $table->foreignId('sucursal_id')->constrained('sucursal')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
