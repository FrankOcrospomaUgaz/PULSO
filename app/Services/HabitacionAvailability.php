<?php

namespace App\Services;

use App\Models\Habitacion;
use App\Models\Movhabitacion;
use App\Models\MovhabitacionHabitacion;
use Illuminate\Support\Facades\DB;

class HabitacionAvailability
{
    public static function hasActiveStay(int $habitacionId): bool
    {
        $activeDetail = MovhabitacionHabitacion::where('habitacion_id', $habitacionId)
            // A closed (C) detail still belongs to its parent movement, but it
            // must not keep the room occupied after checkout.
            ->whereIn('situacion', ['A', 'P'])
            ->whereHas('movhabitacion', function ($query) {
                $query->whereIn('situacion', ['A', 'P']);
            })
            ->exists();

        if ($activeDetail) {
            return true;
        }

        // Legacy single-room stays can lack a pivot row; use the parent room only
        // when there are no detail rows at all (groups can have a different active room).
        return Movhabitacion::where('habitacion_id', $habitacionId)
            ->whereIn('situacion', ['A', 'P'])
            ->whereDoesntHave('movhabitacion_habitacion')
            ->exists();
    }

    /** Mark a checked-out room for cleaning unless another active stay owns it. */
    public static function markCleaningIfNoActiveStay(int $habitacionId): bool
    {
        return DB::transaction(function () use ($habitacionId) {
            $habitacion = Habitacion::whereKey($habitacionId)->lockForUpdate()->first();
            if (! $habitacion) {
                return false;
            }

            $situacion = self::hasActiveStay($habitacionId) ? 'Ocupada' : 'Limpieza';
            $habitacion->update(['situacion' => $situacion]);

            return $situacion === 'Limpieza';
        });
    }

    /** Mark a room available only after its last active stay has ended. */
    public static function markAvailableIfNoActiveStay(int $habitacionId): bool
    {
        return DB::transaction(function () use ($habitacionId) {
            // Check-in also locks this row before creating an active stay.
            $habitacion = Habitacion::whereKey($habitacionId)->lockForUpdate()->first();
            if (! $habitacion) {
                return false;
            }

            if (self::hasActiveStay($habitacionId)) {
                // Repair stale visual state while preserving the active stay.
                $habitacion->update(['situacion' => 'Ocupada']);

                return false;
            }

            $habitacion->update(['situacion' => 'Disponible']);

            return true;
        });
    }
}
