<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class MovreservaHabitacion
 *
 * @property $id
 * @property $movreserva_id
 * @property $habitacion_id
 * @property $principal
 * @property $tarifa
 * @property $fechacheckin
 * @property $fechacheckout
 * @property $dias
 * @property $total
 * @property $situacion
 * @property $json_habitacion
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Habitacion $habitacion
 * @property Movreserva $movreserva
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class MovreservaHabitacion extends Model
{
    use SoftDeletes;

    protected $table = 'movreserva_habitacion';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'movreserva_id',
        'habitacion_id',
        'principal',
        'tarifa',
        'fechacheckin',
        'fechacheckout',
        'dias',
        'total',
        'situacion',
        'json_habitacion',
        'early_checkin',
        'late_checkout'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function habitacion()
    {
        return $this->belongsTo(\App\Models\Habitacion::class, 'habitacion_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function movreserva()
    {
        return $this->belongsTo(\App\Models\Movreserva::class, 'movreserva_id', 'id');
    }
}
