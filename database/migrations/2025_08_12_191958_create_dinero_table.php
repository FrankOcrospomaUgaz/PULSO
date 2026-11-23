<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <- Importar DB para insertar datos

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dinero', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('valor', 8, 2);
            $table->string('tipo', 255)->default('MONEDA')->comment('MONEDA, BILLETE');
            $table->string('descripcion', 255);
            $table->integer('orden')->default(0);
            $table->string('situacion', 1)->default('E');
            $table->timestamps();
            $table->softDeletes();
        });

        // Inserción de datos iniciales
        DB::table('dinero')->insert([
            ['valor' => 0.10, 'tipo' => 'MONEDA', 'descripcion' => '10 céntimos', 'orden' => 1, 'situacion' => 'E', 'created_at' => now(), 'updated_at' => now()],
            ['valor' => 0.20, 'tipo' => 'MONEDA', 'descripcion' => '20 céntimos', 'orden' => 2, 'situacion' => 'E', 'created_at' => now(), 'updated_at' => now()],
            ['valor' => 0.50, 'tipo' => 'MONEDA', 'descripcion' => '50 céntimos', 'orden' => 3, 'situacion' => 'E', 'created_at' => now(), 'updated_at' => now()],
            ['valor' => 1.00, 'tipo' => 'MONEDA', 'descripcion' => '1 sol', 'orden' => 4, 'situacion' => 'E', 'created_at' => now(), 'updated_at' => now()],
            ['valor' => 2.00, 'tipo' => 'MONEDA', 'descripcion' => '2 soles', 'orden' => 5, 'situacion' => 'E', 'created_at' => now(), 'updated_at' => now()],
            ['valor' => 5.00, 'tipo' => 'MONEDA', 'descripcion' => '5 soles', 'orden' => 6, 'situacion' => 'E', 'created_at' => now(), 'updated_at' => now()],
            ['valor' => 10.00, 'tipo' => 'BILLETE', 'descripcion' => '10 soles', 'orden' => 7, 'situacion' => 'E', 'created_at' => now(), 'updated_at' => now()],
            ['valor' => 20.00, 'tipo' => 'BILLETE', 'descripcion' => '20 soles', 'orden' => 8, 'situacion' => 'E', 'created_at' => now(), 'updated_at' => now()],
            ['valor' => 50.00, 'tipo' => 'BILLETE', 'descripcion' => '50 soles', 'orden' => 9, 'situacion' => 'E', 'created_at' => now(), 'updated_at' => now()],
            ['valor' => 100.00, 'tipo' => 'BILLETE', 'descripcion' => '100 soles', 'orden' => 10, 'situacion' => 'E', 'created_at' => now(), 'updated_at' => now()],
            ['valor' => 200.00, 'tipo' => 'BILLETE', 'descripcion' => '200 soles', 'orden' => 11, 'situacion' => 'E', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dinero');
    }
};
