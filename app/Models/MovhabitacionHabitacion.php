<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class MovhabitacionHabitacion
 *
 * @property $id
 * @property $movhabitacion_id
 * @property $habitacion_id
 * @property $principal
 * @property $tarifa
 * @property $fechacheckin
 * @property $fechacheckout
 * @property $dias
 * @property $horas
 * @property $total
 * @property $modalidad
 * @property $situacion
 * @property $json_habitacion
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Habitacion $habitacion
 * @property Movhabitacion $movhabitacion
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class MovhabitacionHabitacion extends Model
{
    use SoftDeletes;

    protected $table = 'movhabitacion_habitacion';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'movhabitacion_id',
        'habitacion_id',
        'principal',
        'tarifa',
        'fechacheckin',
        'fechacheckout',
        'notificado_checkout_10',
        'dias',
        'horas',
        'total',
        'modalidad',
        'situacion',
        'json_habitacion',
        'movventa_id',
        'early_checkin',
        'late_checkout'
    ];

    protected $casts = [
        'notificado_checkout_10' => 'boolean',
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
    public function movhabitacion()
    {
        return $this->belongsTo(\App\Models\Movhabitacion::class, 'movhabitacion_id', 'id');
    }
    public function movventa()
    {
        return $this->belongsTo(\App\Models\Movventum::class, 'movventa_id', 'id');
    }
}
