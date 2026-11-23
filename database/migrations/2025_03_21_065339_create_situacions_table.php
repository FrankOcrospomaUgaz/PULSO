<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('situacion', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion');
            $table->string('color')->nullable(); // color en formato HEX o nombre
            $table->boolean('estado')->default(true); 
            $table->foreignId('sucursal_id')->constrained('sucursal')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes(); // deleted_at para eliminaciones lógicas
        });

        // Insertar situaciones por defecto en cada sucursal
        $situaciones = [
            ['descripcion' => 'PENDIENTE', 'color' => '#FFD700'],
            ['descripcion' => 'EN ATENCIÓN', 'color' => '#28A745'],
            ['descripcion' => 'FINALIZADO', 'color' => '#FF5733'],
            ['descripcion' => 'LIMPIEZA', 'color' => '#ffc107'],
            ['descripcion' => 'DISPONIBLE', 'color' => '#7FFFCC'],
            ['descripcion' => 'OCUPADA', 'color' => '#ff7f7f'],
            ['descripcion' => 'MANTENIMIENTO', 'color' => '#805008'],
        ];

        // Agregar a todas las sucursales existentes
        foreach (DB::table('sucursal')->get() as $sucursal) {
            foreach ($situaciones as $item) {
                DB::table('situacion')->insert([
                    'descripcion' => $item['descripcion'],
                    'color' => $item['color'],
                    'estado' => true,
                    'sucursal_id' => $sucursal->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('situacion');
    }
};
