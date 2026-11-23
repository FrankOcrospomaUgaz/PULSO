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
        Schema::create('facturador', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('empresa_facturador_id')->comment('Id de empresa en facturador');
            $table->decimal('porcentaje_igv', 8, 2)->default(18);
            $table->string('username_wsdl', 255);
            $table->string('password_wsdl', 255);
            $table->string('modo_autenticacion', 1)->default('E');
            $table->string('username_sunat', 255);
            $table->string('password_sunat', 255);
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
        Schema::dropIfExists('facturador');
    }
};
