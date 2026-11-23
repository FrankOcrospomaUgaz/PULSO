<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Movreserva
 *
 * @property $id
 * @property $observacion
 * @property $nropersonas
 * @property $fechavencimiento
 * @property $fechainicio
 * @property $fechasalida
 * @property $fechacancelacion
 * @property $dias
 * @property $moneda
 * @property $tipocambio
 * @property $total
 * @property $habitacionprincipal_id
 * @property $habitacion_principal
 * @property $json_habitaciones
 * @property $estado
 * @property $movimiento_id
 * @property $motivo_id
 * @property $sucursal_id
 * @property $celular
 * @property $email
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Habitacion $habitacion
 * @property Motivoreserva $motivoreserva
 * @property Movimiento $movimiento
 * @property Sucursal $sucursal
 * @property DetMovreservaproducto[] $detMovreservaproductos
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Movreserva extends Model
{
    use SoftDeletes;



    protected $table = 'movreserva';


    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['observacion', 'nropersonas', 'fechavencimiento', 'fechainicio', 'fechasalida', 'fechacancelacion', 'dias', 'moneda', 'tipocambio', 'total', 'descuento', 'adelanto', 'habitacionprincipal_id', 'habitacion_principal', 'json_habitaciones', 'estado', 'movimiento_id', 'motivo_id', 'sucursal_id', 'celular', 'email', 'origen_id', 'json_acompañantes', 'mediopagoadelanto_id', 'preciohabitacion', 'modo'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function habitacion()
    {
        return $this->belongsTo(\App\Models\Habitacion::class, 'habitacionprincipal_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function motivoreserva()
    {
        return $this->belongsTo(\App\Models\Motivoreserva::class, 'motivo_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function movimiento()
    {
        return $this->belongsTo(\App\Models\Movimiento::class, 'movimiento_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id');
    }

    public function origen()
    {
        return $this->belongsTo(\App\Models\Origenreserva::class, 'origen_id', 'id');
    }
    public function mediopago()
    {
        return $this->belongsTo(\App\Models\Mediopago::class, 'mediopagoadelanto_id', 'id');
    }
    public function movventas()
{
    return $this->hasMany(\App\Models\Movventum::class, 'movreserva_id', 'id')
        ->whereHas('movimiento', function ($query) {
            $query->where('situacion', '!=', 'D'); // Excluye los movventum cuyo movimiento.situacion es 'D'
        });
}

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detMovreservaproductos()
    {
        return $this->hasMany(\App\Models\DetMovreservaproducto::class, 'id', 'movreserva_id');
    }
    public function habitaciones()
    {
        return $this->belongsToMany(Habitacion::class, 'movreserva_habitacion', 'movreserva_id', 'habitacion_id')
                    ->wherePivotNull('deleted_at');
    }
    
    public function movreserva_habitacion()
    {
        return $this->hasMany(MovreservaHabitacion::class, 'movreserva_id', 'id');
    }

    public function huespeds()
{
    return $this->hasMany(\App\Models\Huesped::class, 'movreserva_id');
}
public function movhabitacion()
{
    if (!$this->movimiento) {
        return null;
    }

    return \App\Models\Movhabitacion::whereHas('movimiento', function ($q) {
            $q->where('movimiento_id', $this->movimiento_id)
              ->whereNull('deleted_at');
        })
        ->whereNull('deleted_at')
        ->first();
}


}
