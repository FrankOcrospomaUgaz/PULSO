<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Categoriaparametro
 *
 * @property $id
 * @property $descripcion
 * @property $orden
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Parametro[] $parametros
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Categoriaparametro extends Model
{
    use SoftDeletes;


    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['descripcion', 'orden'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function parametros()
    {
        return $this->hasMany(\App\Models\Parametro::class, 'id', 'categoriaparametro_id');
    }
    

}
