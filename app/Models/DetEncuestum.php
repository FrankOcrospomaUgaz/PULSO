<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DetEncuestum
 *
 * @property $id
 * @property $enunciado
 * @property $orden
 * @property $obligatorio
 * @property $opciones
 * @property $situacion
 * @property $categoriapregunta_id
 * @property $categoriapregunta
 * @property $tipopregunta_id
 * @property $encuesta_id
 * @property $sucursal_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Categoriapreguntum $categoriapreguntum
 * @property Encuestum $encuestum
 * @property Sucursal $sucursal
 * @property Tipopreguntum $tipopreguntum
 * @property DetRespuestum[] $detRespuestas
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class DetEncuestum extends Model
{
    use SoftDeletes;


    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['enunciado', 'orden', 'obligatorio', 'opciones', 'situacion', 'categoriapregunta_id', 'categoriapregunta', 'tipopregunta_id', 'encuesta_id', 'sucursal_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function categoriapregunta()
    {
        return $this->belongsTo(\App\Models\Categoriapreguntum::class, 'categoriapregunta_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function encuesta()
    {
        return $this->belongsTo(\App\Models\Encuestum::class, 'encuesta_id', 'id');
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
    public function tipopregunta()
    {
        return $this->belongsTo(\App\Models\Tipopreguntum::class, 'tipopregunta_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detRespuestas()
    {
        return $this->hasMany(\App\Models\DetRespuestum::class, 'id', 'det_encuesta_id');
    }
    
    public function getSituacionAttribute($value)
    {
        return $value === 'E' ? 'Activado' : 'Desactivado';
    }

}
