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
        Schema::create('rel_parametroempresa', function (Blueprint $table) {
            $table->id();
            $table->string('valor', 255);
            $table->unsignedBigInteger('parametro_id');
            $table->unsignedBigInteger('empresa_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            // Foreign key constraints
            $table->foreign('empresa_id')
                ->references('id')
                ->on('empresas')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('parametro_id')
                ->references('id')
                ->on('parametros')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rel_parametroempresa');
    }
};
