<?php

namespace Tests\Unit;

use App\Models\Habitacion;
use App\Models\Movhabitacion;
use App\Models\MovhabitacionHabitacion;
use App\Services\HabitacionAvailability;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HabitacionAvailabilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => false,
        ]);
        DB::purge('sqlite');

        Schema::create('habitacion', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->nullable();
            $table->string('situacion')->default('Disponible');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('movhabitacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('habitacion_id')->nullable();
            $table->string('situacion')->default('A');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('movhabitacion_habitacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movhabitacion_id');
            $table->unsignedBigInteger('habitacion_id');
            $table->string('situacion')->default('A');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        parent::tearDown();
    }

    public function test_closed_movement_detail_does_not_keep_room_occupied(): void
    {
        $room = $this->room('Ocupada');
        $stay = $this->stay($room, 'C');
        $this->detail($stay, $room, 'C');

        $this->assertFalse(HabitacionAvailability::hasActiveStay($room->id));
        $this->assertTrue(HabitacionAvailability::markCleaningIfNoActiveStay($room->id));
        $this->assertSame('Limpieza', $room->fresh()->situacion);
    }

    public function test_closed_room_in_a_group_is_cleaned_while_other_active_room_stays_occupied(): void
    {
        $closedRoom = $this->room('Ocupada');
        $activeRoom = $this->room('Ocupada');
        $stay = $this->stay($closedRoom, 'A');
        $this->detail($stay, $closedRoom, 'C');
        $this->detail($stay, $activeRoom, 'A');

        $this->assertTrue(HabitacionAvailability::markCleaningIfNoActiveStay($closedRoom->id));
        $this->assertFalse(HabitacionAvailability::markCleaningIfNoActiveStay($activeRoom->id));
        $this->assertSame('Limpieza', $closedRoom->fresh()->situacion);
        $this->assertSame('Ocupada', $activeRoom->fresh()->situacion);
    }

    public function test_another_active_stay_prevents_releasing_the_room(): void
    {
        $room = $this->room('Ocupada');
        $closedStay = $this->stay($room, 'C');
        $this->detail($closedStay, $room, 'C');
        $activeStay = $this->stay($room, 'A');
        $this->detail($activeStay, $room, 'A');

        $this->assertTrue(HabitacionAvailability::hasActiveStay($room->id));
        $this->assertFalse(HabitacionAvailability::markCleaningIfNoActiveStay($room->id));
        $this->assertSame('Ocupada', $room->fresh()->situacion);
    }

    public function test_legacy_active_single_room_without_pivot_remains_occupied(): void
    {
        $room = $this->room('Ocupada');
        $this->stay($room, 'A');

        $this->assertTrue(HabitacionAvailability::hasActiveStay($room->id));
        $this->assertFalse(HabitacionAvailability::markCleaningIfNoActiveStay($room->id));
        $this->assertSame('Ocupada', $room->fresh()->situacion);
    }

    private function room(string $status): Habitacion
    {
        return Habitacion::create(['numero' => 'T' . random_int(100, 999), 'situacion' => $status]);
    }

    private function stay(Habitacion $room, string $status): Movhabitacion
    {
        return Movhabitacion::create(['habitacion_id' => $room->id, 'situacion' => $status]);
    }

    private function detail(Movhabitacion $stay, Habitacion $room, string $status): MovhabitacionHabitacion
    {
        return MovhabitacionHabitacion::create([
            'movhabitacion_id' => $stay->id,
            'habitacion_id' => $room->id,
            'situacion' => $status,
        ]);
    }
}
