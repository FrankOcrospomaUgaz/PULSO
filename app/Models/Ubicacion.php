<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Ubicacion
 *
 * @property $id
 * @property $codigo
 * @property $descripcion
 * @property $totalfilas
 * @property $totalcolumnas
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property RelProductosucursal[] $relProductosucursals
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Ubicacion extends Model
{
    use SoftDeletes;
    protected $table = 'ubicacion';


    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['codigo', 'descripcion', 'totalfilas', 'totalcolumnas'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function relProductosucursals()
    {
        return $this->hasMany(\App\Models\RelProductosucursal::class, 'id', 'ubicacion_id');
    }
    

}
