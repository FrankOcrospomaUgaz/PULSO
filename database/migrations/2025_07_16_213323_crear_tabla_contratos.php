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
       Schema::create('contrato', function (Blueprint $table) {
    $table->id();
    $table->string('numero_contrato')->unique()->comment('Código o número del contrato');
    $table->date('fecha_inicio');
    $table->date('fecha_fin');
    $table->unsignedTinyInteger('dia_pago')->comment('Día del mes que se realiza el pago');
    $table->decimal('monto', 10, 2);
    $table->decimal('garantia', 10, 2);
    $table->boolean('cubre_luz')->default(false);
    $table->boolean('cubre_agua')->default(false);
    $table->text('descripcion')->nullable();
    $table->text('informacion_adicional')->nullable();

    $table->foreignId('ambiente_id')->constrained('ambiente')->onDelete('cascade');
    $table->foreignId('personamaestro_id')->constrained('personamaestro')->onDelete('cascade');
    $table->foreignId('edificio_id')->constrained('edificio')->onDelete('cascade');
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
