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
        Schema::create('ubigeo', function (Blueprint $table) {
            $table->id(); // BIGINT AUTO_INCREMENT
            $table->string('descripcion', 255); // VARCHAR(255), NOT NULL
            $table->string('codigo', 6); // VARCHAR(6), NOT NULL
            $table->string('inei', 6); // VARCHAR(6), NOT NULL
            $table->string('tipo', 1); // VARCHAR(1), NOT NULL
            $table->foreignId('ubigeo_id')->nullable()->constrained('ubigeo')->cascadeOnUpdate()->cascadeOnDelete(); // Relación con sí misma (autorreferencial)
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at para soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ubigeo');
    }
};
