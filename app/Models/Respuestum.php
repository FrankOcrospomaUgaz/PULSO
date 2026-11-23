<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Respuestum
 *
 * @property $id
 * @property $codigo
 * @property $fecha
 * @property $persona_id
 * @property $persona
 * @property $encuesta_id
 * @property $movimiento_id
 * @property $movimiento
 * @property $sucursal_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Encuestum $encuestum
 * @property Movimiento $movimiento
 * @property Sucursal $sucursal
 * @property DetRespuestum[] $detRespuestas
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Respuestum extends Model
{
    use SoftDeletes;


    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['codigo', 'fecha', 'persona_id', 'persona', 'encuesta_id', 'movimiento_id', 'movimiento', 'sucursal_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function encuestum()
    {
        return $this->belongsTo(\App\Models\Encuestum::class, 'encuesta_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function Movimiento()
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
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detRespuestas()
    {
        return $this->hasMany(\App\Models\DetRespuestum::class, 'respuesta_id', 'id');
    }
        public function Persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

}
