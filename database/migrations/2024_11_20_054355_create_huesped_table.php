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
        Schema::create('huesped', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('persona_id');
            $table->unsignedBigInteger('movhabitacion_id');
            $table->timestamps();
            $table->softDeletes();

             // Foreign keys
            $table->foreign('persona_id')->references('id')->on('persona')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('movhabitacion_id')->references('id')->on('movhabitacion')->onDelete('cascade')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('huesped');
    }
};
