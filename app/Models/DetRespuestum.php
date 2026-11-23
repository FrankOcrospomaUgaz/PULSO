<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DetRespuestum
 *
 * @property $id
 * @property $det_encuesta_id
 * @property $respuesta_id
 * @property $sucursal_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property DetEncuestum $detEncuestum
 * @property Respuestum $respuestum
 * @property Sucursal $sucursal
 * @property ValordetRespuestum[] $valordetRespuestas
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class DetRespuestum extends Model
{
    use SoftDeletes;


    protected $table = 'det_respuesta';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['det_encuesta_id', 'respuesta_id', 'sucursal_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function detEncuestum()
    {
        return $this->belongsTo(\App\Models\DetEncuestum::class, 'det_encuesta_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function respuestum()
    {
        return $this->belongsTo(\App\Models\Respuestum::class, 'respuesta_id', 'id');
    }
    
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
    public function valordetRespuestas()
    {
        return $this->hasMany(\App\Models\ValordetRespuestum::class,  'det_respuesta_id', 'id');
    }
    

}
