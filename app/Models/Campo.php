<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Campo
 *
 * @property $id
 * @property $descripcion
 * @property $comentario
 * @property $longitud
 * @property $diccionario
 * @property $validacion
 * @property $msgvalidacion
 * @property $longitudreporte
 * @property $alineacionreporte
 * @property $tabla_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Tabla $tabla
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Campo extends Model
{
    use SoftDeletes;

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */

    protected $fillable = ['descripcion', 'comentario', 'longitud', 'diccionario', 'validacion', 'msgvalidacion', 'longitudreporte', 'alineacionreporte', 'tabla_id'];

    /**
     * Default attribute values
     *
     * @var array
     */
        protected $attributes = [
            'longitud' => 0,
            'diccionario' => '',
            'validacion' => 'N',
            'msgvalidacion' => '',
            'longitudreporte' => 0,
            'alineacionreporte' => 'C'
        ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function Tabla()
    {
        return $this->belongsTo(\App\Models\Tabla::class, 'tabla_id');
    }
}

