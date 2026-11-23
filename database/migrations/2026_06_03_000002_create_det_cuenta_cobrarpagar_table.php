<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('det_cuenta_cobrarpagar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuenta_cobrarpagar_id')->constrained('cuenta_cobrarpagar')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('movcaja_id')->constrained('movcaja')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('situacion', 1)->default('E');
            $table->foreignId('sucursal_id')->constrained('sucursal')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['cuenta_cobrarpagar_id', 'movcaja_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('det_cuenta_cobrarpagar');
    }
};
