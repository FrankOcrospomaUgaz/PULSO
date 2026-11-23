<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class RelSeccionsucursal
 *
 * @property $situacion
 * @property $seccion_id
 * @property $sucursal_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Seccione $seccione
 * @property Sucursal $sucursal
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class RelSeccionsucursal extends Model
{
    use SoftDeletes;

    protected $table = 'rel_seccionsucursal';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['situacion', 'seccion_id', 'sucursal_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function seccione()
    {
        return $this->belongsTo(\App\Models\Seccione::class, 'seccion_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id');
    }
    public function getSituacionAttribute($value)
    {
        return $value === 'A' ? 'Activado' : 'Desactivado';
    }

}
