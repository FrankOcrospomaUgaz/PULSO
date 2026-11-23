<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Modulo
 *
 * @property $id
 * @property $descripcion
 * @property $abreviatura
 * @property $orden
 * @property $icono
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Modulo extends Model
{
    use SoftDeletes;


    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['descripcion', 'abreviatura', 'orden', 'icono'];


    public function getSituacionAttribute($value)
    {
        return $value === 'A' ? 'Aperturada' : 'Cerrada';
    }


}
