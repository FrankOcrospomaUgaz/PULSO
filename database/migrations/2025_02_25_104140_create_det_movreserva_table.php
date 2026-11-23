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
        Schema::create('det_movreservaproducto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movreserva_id')->constrained('movreserva')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('producto')->onDelete('cascade');
            $table->json('json_producto');
            $table->decimal('cantidad', 24, 6);
            $table->decimal('monto', 24, 6);
            $table->text('comentario');
            $table->string('situacion', 1)->default('E');
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
        Schema::dropIfExists('det_movreserva');
    }
};
