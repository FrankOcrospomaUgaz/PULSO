<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Encuestum
 *
 * @property $id
 * @property $uuid
 * @property $nombre
 * @property $descripcion
 * @property $fecha
 * @property $fechavencimiento
 * @property $mensaje_finalizacion
 * @property $imprimible
 * @property $estado
 * @property $situacion
 * @property $sucursal_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Sucursal $sucursal
 * @property DetEncuestum[] $detEncuestas
 * @property Respuestum[] $respuestas
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Encuestum extends Model
{
    use SoftDeletes;

    

    protected $table = 'encuesta';

    protected $perPage = 10;
    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['uuid', 'nombre', 'descripcion', 'fecha', 'fechavencimiento', 'mensaje_finalizacion', 'imprimible', 'estado', 'situacion', 'sucursal_id', 'validacion'];


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
    public function detEncuestas()
    {
        return $this->hasMany(\App\Models\DetEncuestum::class, 'id', 'encuesta_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function respuestas()
    {
        return $this->hasMany(\App\Models\Respuestum::class,  'encuesta_id', 'id');
    }
    public function getSituacionAttribute($value)
    {
        return $value === 'E' ? 'Activado' : 'Desactivado';
    }

}
