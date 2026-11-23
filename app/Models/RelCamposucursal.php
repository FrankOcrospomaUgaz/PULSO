<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class RelCamposucursal
 *
 * @property $descripcion
 * @property $tipo
 * @property $orden
 * @property $diccionario
 * @property $situacion
 * @property $campo_id
 * @property $tabla_id
 * @property $sucursal_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Campo $campo
 * @property Sucursal $sucursal
 * @property Tabla $tabla
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class RelCamposucursal extends Model
{
    use SoftDeletes;

    protected $table = 'rel_camposucursal';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['tipo', 'orden', 'situacion', 'campo_id', 'tabla_id', 'sucursal_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function campo()
    {
        return $this->belongsTo(\App\Models\Campo::class, 'campo_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tabla()
    {
        return $this->belongsTo(\App\Models\Tabla::class, 'tabla_id', 'id');
    }
    public function getTipoAttribute($value)
    {
        return $value === 'G' ? 'Grilla': 'BD' ;
    }

    public function getSituacionAttribute($value)
    {
        return $value ? 'Activado' : 'Desactivado';
    }

}
