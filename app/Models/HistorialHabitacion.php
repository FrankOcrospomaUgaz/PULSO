<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class HistorialHabitacion
 *
 * @property $id
 * @property $situacion_pago
 * @property $situacion_checkout
 * @property $movhabitacion_id
 * @property $relturnocaja_id
 * @property $sucursal_id
 * @property $created_at
 * @property $updated_at
 *
 * @property Movhabitacion $movhabitacion
 * @property RelTurnocaja $relTurnocaja
 * @property Sucursal $sucursal
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class HistorialHabitacion extends Model
{
    

    protected $table = 'historial_habitacion';


    protected $perPage = 10;


    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['situacion_pago', 'situacion_checkout', 'movhabitacion_id', 'relturnocaja_id', 'sucursal_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function movhabitacion()
    {
        return $this->belongsTo(\App\Models\Movhabitacion::class, 'movhabitacion_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function relTurnocaja()
    {
        return $this->belongsTo(\App\Models\RelTurnocaja::class, 'relturnocaja_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id');
    }
    

}
