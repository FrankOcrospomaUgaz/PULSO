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
        Schema::create('solicitud', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_solicitud', 255);
            $table->json('data');
            $table->timestamp('fecha');
            $table->timestamp('fechaenvio')->nullable();
            $table->timestamp('fecharespuesta')->nullable();
            $table->timestamp('fechaanulacion')->nullable();
            $table->string('estado', 1);
            $table->text('nombre')->nullable();
            $table->decimal('total', 8, 2)->nullable();
            $table->string('moneda', 255)->nullable();
            $table->string('tipo_documento', 1);
            $table->string('serie', 8);
            $table->bigInteger('correlativo');
            $table->string('ticket_solicitud', 20)->nullable();
            $table->foreignId('persona_id')->nullable()->constrained('persona')->onDelete('set null');
            $table->json('json_persona')->nullable()->comment('Datos de cliente');
            $table->foreignId('sucursal_id')->constrained('sucursal')->onDelete('cascade');
            $table->json('json_sucursal')->comment('Datos de sucursal relacionados con la solicitud');
            $table->foreignId('facturador_id')->constrained('facturador')->onDelete('cascade');
            $table->json('json_facturador')->comment('Si quiero hacer algo con esta solicitud, voy a tener que usar los datos de facturación que fueron usados para crearla');
            $table->foreignId('movimiento_id')->nullable()->constrained('movimiento')->onDelete('cascade');
            $table->bigInteger('solicitud_facturador_id')->nullable()->comment('Id de solicitud en servidor');
            $table->integer('error_code')->nullable();
            $table->text('error_text')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('public_name', 255)->nullable();

            // Índices y claves primarias
            $table->index('estado');
            $table->index('tipo_documento');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud');
    }
};
