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
        Schema::create('rel_turnocaja', function (Blueprint $table) {
            $table->id(); // id BIGINT PRIMARY KEY
            $table->timestamp('fechainicio')->nullable(false); // NOT NULL
            $table->timestamp('fechafin')->nullable(); // Puede ser NULL
            $table->string('situacion', 1)->default('A'); // VARCHAR(1), DEFAULT 'A'
            $table->unsignedBigInteger('movcajainicio_id'); // FK de movcaja inicio
            $table->unsignedBigInteger('movcajafin_id')->nullable(); // FK de movcaja fin (NULLABLE)
            $table->unsignedBigInteger('sucursal_id'); // FK de sucursal
            $table->timestamps(); // created_at y updated_at
            $table->softDeletes(); // deleted_at

            // Relaciones de clave foránea
            $table->foreign('movcajainicio_id')
                ->references('id')
                ->on('movcaja')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('movcajafin_id')
                ->references('id')
                ->on('movcaja')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('sucursal_id')
                ->references('id')
                ->on('sucursal')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rel_turnocaja');
    }
};
