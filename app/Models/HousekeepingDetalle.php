<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class HousekeepingDetalle
 *
 * @property $id
 * @property $jornada_id
 * @property $habitacion_id
 * @property $inicio_limpieza
 * @property $fin_limpieza
 * @property $tiempo
 * @property $estado
 * @property $observaciones
 * @property $empleado_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Persona $persona
 * @property Habitacion $habitacion
 * @property Housekeeping $housekeeping
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class HousekeepingDetalle extends Model
{
    use SoftDeletes;


    protected $table = 'housekeeping_detalle';
    protected $perPage = 10;
    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['jornada_id', 'habitacion_id', 'inicio_limpieza', 'fin_limpieza', 'tiempo', 'estado', 'observaciones', 'empleado_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function persona()
    {
        return $this->belongsTo(\App\Models\Persona::class, 'empleado_id', 'id');
    }
    
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
    public function housekeeping()
    {
        return $this->belongsTo(\App\Models\Housekeeping::class, 'jornada_id', 'id');
    }
    

}
