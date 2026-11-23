<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Rolpersona
 *
 * @property $persona_id
 * @property $rol_id
 * @property $sucursal_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Persona $persona
 * @property Rol $rol
 * @property Sucursal $sucursal
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Rolpersona extends Model
{
    use SoftDeletes;

    protected $table = 'rolpersona';

    protected $perPage = 20;

    /**
     * Indica que no hay un campo 'id' en la tabla.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Desactivar claves primarias autoincrementables
     *
     * @var bool
     */

    /**
     * Desactivar las marcas de tiempo automáticas de Laravel.
     *
     * @var bool
     */
    public $timestamps = true; // Si deseas seguir usando timestamps

    /**
     * Definir que la clave primaria es compuesta.
     *
     * @var array
     */
    protected $primaryKey = ['persona_id', 'rol_id', 'sucursal_id'];

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['persona_id', 'rol_id', 'sucursal_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function persona()
    {
        return $this->belongsTo(\App\Models\Persona::class, 'persona_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function rol()
    {
        return $this->belongsTo(\App\Models\Rol::class, 'rol_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id');
    }
}
