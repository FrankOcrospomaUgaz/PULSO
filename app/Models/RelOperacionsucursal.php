<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class RelOperacionsucursal
 *
 * @property $orden
 * @property $situacion
 * @property $operacion_id
 * @property $tabla_id
 * @property $sucursal_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Operacione $operacione
 * @property Sucursal $sucursal
 * @property Tabla $tabla
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class RelOperacionsucursal extends Model
{
    use SoftDeletes;

    protected $table = 'rel_operacionsucursal';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['orden', 'situacion', 'operacion_id', 'tabla_id', 'sucursal_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
// En el modelo RelOperacionsucursal
public function operacione()
{
    return $this->belongsTo(\App\Models\Operacione::class, 'operacion_id', 'id');
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
    public function getSituacionAttribute($value)
    {
        return $value ? 'Activado' : 'Desactivado';
    }

}
