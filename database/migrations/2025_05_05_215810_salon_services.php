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
        Schema::create('salon_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained('salon')->onDelete('cascade');
            $table->string('titulo'); // Ej: Cumpleaños y fiestas privadas
            $table->text('descripcion');
            $table->text('icono')->nullable(); // Puede ser clase de ícono o URL
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
