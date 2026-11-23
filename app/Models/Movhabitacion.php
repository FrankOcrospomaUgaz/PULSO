<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Movhabitacion
 *
 * @property $id
 * @property $descuento
 * @property $fechaingreso
 * @property $fechasalida
 * @property $dias
 * @property $total
 * @property $preciohabitacion
 * @property $situacion
 * @property $comentario
 * @property $moneda
 * @property $habitacion_id
 * @property $movimiento_id
 * @property $early_checkin
 * @property $late_checkout
 * @property $day_use
 * @property $sucursal_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Sucursal $sucursal
 * @property Huesped[] $huespeds
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Movhabitacion extends Model
{
    use SoftDeletes;


    protected $table = 'movhabitacion';


    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['descuento', 'fechaingreso', 'fechasalida', 'dias', 'total', 'preciohabitacion', 'situacion', 'comentario', 'moneda', 'habitacion_id', 'movimiento_id', 'early_checkin', 'late_checkout', 'day_use', 'sucursal_id', 'fecha_salida_real', 'horas', 'modalidad', 'movreserva_id', 'modo'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id');
    }
    public function movimiento()
    {
        return $this->belongsTo(\App\Models\Movimiento::class, 'movimiento_id', 'id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function huespeds()
    {
        return $this->hasMany(\App\Models\Huesped::class, 'movhabitacion_id',  'id');
    }
    
    public function habitacion()
    {
        return $this->belongsTo(\App\Models\Habitacion::class, 'habitacion_id');
    }
    public function movreserva()
    {
        return $this->belongsTo(\App\Models\Movreserva::class, 'movreserva_id');
    }
public function movventas()
{
    return $this->hasMany(\App\Models\Movventum::class, 'movhabitacion_id', 'id')
        ->whereHas('movimiento', function ($query) {
            $query->where('situacion', '!=', 'D'); // Excluye los movventum cuyo movimiento.situacion es 'D'
        });
}

public function habitaciones()
{
    return $this->belongsToMany(
        Habitacion::class,
        'movhabitacion_habitacion',
        'movhabitacion_id',
        'habitacion_id'
    )->whereNull('movhabitacion_habitacion.deleted_at');
}

    public function movhabitacion_habitacion()
    {
        return $this->hasMany(MovhabitacionHabitacion::class, 'movhabitacion_id', 'id');
    }

        public function detmovhabitacion()
    {
        return $this->hasMany(DetMovhabitacion::class, 'movhabitacion_id', 'id');
    }
}
