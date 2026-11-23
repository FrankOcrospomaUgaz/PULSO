<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * Class Operacione
 *
 * @property $id
 * @property $tipo
 * @property $descripcion
 * @property $comentario
 * @property $accion
 * @property $imagen
 * @property $versi
 * @property $parametros
 * @property $tabla_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Tabla $tabla
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Operacione extends Model
{
    use SoftDeletes;


    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['tipo', 'descripcion', 'comentario', 'accion', 'imagen', 'color', 'parametros', 'tabla_id', 'estado'];


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
    public function getTipoAttribute($value)
    {
        return $value === 't' ? 'Tabla' : ($value === 'r' ? 'Registro' : $value);
    }
   
    
    public function getIconClassAttribute()
    {
        if (preg_match('/class=["\']([^"\']+)["\']/', $this->imagen, $m)) {
            return $m[1];
        }
        return '';
    }


}
