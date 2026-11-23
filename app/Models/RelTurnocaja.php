<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class RelTurnocaja
 *
 * @property $id
 * @property $fechainicio
 * @property $fechafin
 * @property $situacion
 * @property $movcajainicio_id
 * @property $movcajafin_id
 * @property $sucursal_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Movcaja $movcaja
 * @property Movcaja $movcaja
 * @property Sucursal $sucursal
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class RelTurnocaja extends Model
{
    use SoftDeletes;


    protected $table = 'rel_turnocaja';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['fechainicio', 'fechafin', 'situacion', 'movcajainicio_id', 'movcajafin_id', 'sucursal_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function movcajafin()
    {
        return $this->belongsTo(\App\Models\Movcaja::class, 'movcajafin_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function movcajainicio()
    {
        return $this->belongsTo(\App\Models\Movcaja::class, 'movcajainicio_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id');
    }
    
    public function scopeConCajaActiva($query, $cajaId)
    {
        return $query->whereHas('movcajainicio', function ($q) use ($cajaId) {
            $q->where('caja_id', $cajaId);
        })->where('situacion', 'A')->where('fechafin', null);
    }
    public function getSituacionAttribute($value)
    {
        return $value === 'A' ? 'Activado' : 'Desactivado';
    }

}
