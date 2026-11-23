<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Unidad
 *
 * @property $id
 * @property $descripcion
 * @property $abreviatura
 * @property $tipo
 * @property $sunat
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Producto[] $productos
 * @property Producto[] $productos
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Unidad extends Model
{
    use SoftDeletes;

    protected $table = 'unidad';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['descripcion', 'abreviatura', 'tipo', 'sunat'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productos()
    {
        return $this->hasMany(\App\Models\Producto::class, 'id', 'medidapeso_id');
    }
    

    

}
