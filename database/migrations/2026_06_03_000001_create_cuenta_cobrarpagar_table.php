<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuenta_cobrarpagar', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 255);
            $table->foreignId('movcaja_id')->constrained('movcaja')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('tipo', 255)->default('COBRAR')->comment('COBRAR => Ingreso, PAGAR => Egreso');
            $table->string('estado', 255)->default('NUEVO')->comment('NUEVO => Nueva cuenta, PAGANDO => Se esta pagando, PAGADO => Se completo');
            $table->timestamp('fecha');
            $table->timestamp('fechavencimiento')->comment('Deberia ser pagado en la fecha');
            $table->timestamp('fechapago')->nullable()->comment('Pagado en la fecha. Si hay pagos parciales, la fecha de pago es la ultima');
            $table->string('moneda', 255)->default('PEN');
            $table->decimal('tipocambio', 8, 3);
            $table->decimal('totalpagado', 8, 2)->default(0);
            $table->string('situacion', 1)->default('E');
            $table->foreignId('sucursal_id')->constrained('sucursal')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['movcaja_id', 'tipo']);
            $table->index(['sucursal_id', 'tipo', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuenta_cobrarpagar');
    }
};
