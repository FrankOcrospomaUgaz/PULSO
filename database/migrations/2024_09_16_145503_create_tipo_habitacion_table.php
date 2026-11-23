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
        Schema::create('tipohabitacion', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 100);
            $table->integer('capacidad')->nullable();
            $table->decimal('precio', 10, 2);
            $table->boolean('estado', 1)->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipohabitacion');
    }
};
