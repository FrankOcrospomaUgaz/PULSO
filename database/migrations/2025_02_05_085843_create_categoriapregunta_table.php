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
        Schema::create('categoriapregunta', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->char('situacion', 1)->default('E');
            $table->foreignId('sucursal_id')->constrained('sucursal')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('categoriapregunta');
    }
};
