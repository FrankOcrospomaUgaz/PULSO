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
        Schema::create('producto', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50);
            $table->string('descripcion', 255);
            $table->string('abreviatura', 255);
            $table->string('tipo', 255)->comment('PRODUCTO, COMPONENTE');
            $table->unsignedBigInteger('categoria_id');
            $table->unsignedBigInteger('marca_id')->nullable();
            $table->unsignedBigInteger('unidadbase_id');
            $table->unsignedBigInteger('medidapeso_id')->nullable();
            $table->string('kardex', 1)->default('N');
            $table->string('compuesto', 1)->default('N');
            $table->string('autoproduccion', 255)->default('S')->comment('S => Sí, N => No');
            $table->decimal('peso', 24, 6);
            $table->text('comentario');
            $table->text('imagen');
            $table->string('complemento', 255)->default('NO')->comment('NO => No permite complementos, HAS => Tiene complementos, IS => Es complemento');
            $table->string('modocomplemento', 255)->default('')->comment('TODO => Los mismos complementos son gratis, CANTIDAD => La cantidad de complementos es gratis');
            $table->unsignedBigInteger('empresa_id');
            $table->timestamps();
            $table->softDeletes();
            $table->string('clasificacion', 255)->default('BIEN')->comment('BIEN, SERVICIO');
            $table->string('nro_parte', 255)->default('');
            $table->text('caracteristicas')->default('');
            $table->unsignedBigInteger('distribuidor_id')->nullable();
            $table->text('imagen2')->default('');
            $table->text('imagen3')->default('');

            // Definición de claves foráneas
            $table->foreign('categoria_id')->references('id')->on('categoria')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('distribuidor_id')->references('id')->on('distribuidor')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('marca_id')->references('id')->on('marca')->onUpdate('restrict')->onDelete('set null');
            $table->foreign('medidapeso_id')->references('id')->on('unidad')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('unidadbase_id')->references('id')->on('unidad')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto');
    }
};
