<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Perfile
 *
 * @property $id
 * @property $descripcion
 * @property $abreviatura
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Perfile extends Model
{
    use SoftDeletes;


    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['descripcion', 'abreviatura'];
    public function __toString()
    {
        // Cambia esto a algo que tenga sentido, por ejemplo 'num_serie'
        return $this->descripcion;  // Devuelve el número de serie al convertir a cadena
    }


}
