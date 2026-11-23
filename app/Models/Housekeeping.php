<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Housekeeping
 *
 * @property $id
 * @property $fecha_programada
 * @property $tiempo_total
 * @property $estado
 * @property $observaciones
 * @property $sucursal_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Sucursal $sucursal
 * @property HousekeepingDetalle[] $housekeepingDetalles
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Housekeeping extends Model
{
    use SoftDeletes;


    protected $table = 'housekeeping';
    protected $perPage = 10;
    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['fecha_programada', 'tiempo_total', 'estado', 'observaciones', 'sucursal_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function housekeepingDetalles()
    {
        return $this->hasMany(\App\Models\HousekeepingDetalle::class, 'jornada_id', 'id');
    }
    

}
