<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Parametro
 *
 * @property $id
 * @property $descripcion
 * @property $valor
 * @property $tipo
 * @property $obligatorio
 * @property $categoriaparametro_id
 * @property $tabla_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Categoriaparametro $categoriaparametro
 * @property Tabla $tabla
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Parametro extends Model
{
    use SoftDeletes;


    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['descripcion', 'valor', 'tipo', 'obligatorio', 'categoriaparametro_id', 'tabla_id', 'estado'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function categoriaparametro()
    {
        return $this->belongsTo(\App\Models\Categoriaparametro::class, 'categoriaparametro_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tabla()
    {   
        return $this->belongsTo(\App\Models\Tabla::class, 'tabla_id', 'id');
    }

    public function getEstadoAttribute($value)
    {
        return $value ? 'Normal' : 'Eliminado';
    }
    

}
