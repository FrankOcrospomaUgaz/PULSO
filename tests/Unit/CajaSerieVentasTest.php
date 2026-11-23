<?php

use App\Http\Controllers\CajaController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config([
        'database.default' => 'sqlite',
        'database.connections.sqlite.database' => ':memory:',
    ]);

    DB::purge('sqlite');

    Schema::create('tipodocumento', function (Blueprint $table) {
        $table->id();
        $table->string('abreviatura');
    });

    Schema::create('movimiento', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('tipodocumento_id');
    });

    Schema::create('movventa', function (Blueprint $table) {
        $table->id();
        $table->string('serie')->nullable();
        $table->unsignedBigInteger('movimiento_id');
        $table->unsignedBigInteger('sucursal_id');
        $table->timestamp('updated_at')->nullable();
    });
});

test('actualiza las series de todas las ventas solo en la sucursal de la caja', function () {
    DB::table('tipodocumento')->insert([
        ['id' => 4, 'abreviatura' => 'B'],
        ['id' => 10, 'abreviatura' => 'F'],
    ]);

    DB::table('movimiento')->insert([
        ['id' => 101, 'tipodocumento_id' => 4],
        ['id' => 102, 'tipodocumento_id' => 10],
        ['id' => 103, 'tipodocumento_id' => 4],
    ]);

    DB::table('movventa')->insert([
        ['id' => 1, 'serie' => 'B001', 'movimiento_id' => 101, 'sucursal_id' => 7],
        ['id' => 2, 'serie' => 'F001', 'movimiento_id' => 102, 'sucursal_id' => 7],
        ['id' => 3, 'serie' => 'B999', 'movimiento_id' => 103, 'sucursal_id' => 8],
    ]);

    $metodo = new ReflectionMethod(CajaController::class, 'actualizarSerieVentasSucursal');
    $metodo->setAccessible(true);
    $metodo->invoke(new CajaController, 7, '010');

    expect(DB::table('movventa')->orderBy('id')->pluck('serie')->all())
        ->toBe(['B010', 'F010', 'B999']);
});
