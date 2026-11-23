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
        Schema::create('secciones', function (Blueprint $table) {
            $table->id(); // id bigint NOT NULL DEFAULT nextval('seccion_id_seq'::regclass)
            $table->string('descripcion', 255)->comment('Descripción de la sección'); // descripcion character varying(255) NOT NULL
            $table->string('abreviatura', 255)->comment('Abreviatura de la sección'); // abreviatura character varying(255) NOT NULL
            $table->integer('orden')->default(0)->comment('Orden de la sección'); // orden integer NOT NULL DEFAULT 0
            $table->text('icono')->comment('Icono de la sección'); // icono text NOT NULL
            $table->string('color', 255)->comment('Color asociado a la sección'); // color character varying(255) NOT NULL
            $table->text('subtitulo')->comment('Subtítulo de la sección'); // subtitulo text NOT NULL
            $table->text('comentario')->comment('Comentario adicional sobre la sección'); // comentario text NOT NULL
            $table->string('situacion', 1)->default('E')->comment('Situación de la sección'); // situacion character varying(1) NOT NULL DEFAULT 'E'
            $table->timestamps(); // created_at, updated_at timestamps
            $table->softDeletes(); // deleted_at timestamp for soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('secciones', function (Blueprint $table) {
            //
        });
    }
};
