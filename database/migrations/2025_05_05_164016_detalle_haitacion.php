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
        Schema::create('detalle_habitacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habitacion_id')->constrained('habitacion')->onDelete('cascade');
            $table->string('descripcion');
            $table->text('icono')->nullable(); 
            $table->char('situacion', 1)->default('A'); 
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
