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
        Schema::create('salon_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained('salon')->onDelete('cascade');
            $table->string('nombre'); // Ej: Paquete completo
            $table->decimal('precio', 10, 2);
            $table->text('content')->nullable(); // html o texto enriquecido
            $table->text('imagen')->nullable(); // Path o URL de imagen
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
