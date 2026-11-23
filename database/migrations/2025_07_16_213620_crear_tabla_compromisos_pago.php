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
       Schema::create('compromiso_pago', function (Blueprint $table) {
    $table->id();
    $table->foreignId('contrato_id')->constrained('contrato')->onDelete('cascade');
    $table->foreignId('personamaestro_id')->constrained('personamaestro')->onDelete('cascade');
    $table->foreignId('ambiente_id')->constrained('ambiente')->onDelete('cascade');

    $table->date('fecha_vencimiento');
    $table->date('fecha_pago')->nullable();
    $table->integer('numero_cuota');
    $table->decimal('monto', 10, 2);
    $table->enum('forma_pago', ['efectivo', 'tarjeta', 'deposito', 'billetera'])->nullable();
    $table->enum('estado', ['pendiente', 'pagado', 'vencido'])->default('pendiente');
    $table->foreignId('sucursal_id')->constrained('sucursal')->onDelete('cascade');

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
