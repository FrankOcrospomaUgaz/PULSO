<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Ubigeo
 *
 * @property $id
 * @property $descripcion
 * @property $codigo
 * @property $inei
 * @property $tipo
 * @property $ubigeo_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Ubigeo $ubigeo
 * @property Sucursal[] $sucursals
 * @property Ubigeo[] $ubigeos
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Ubigeo extends Model
{
    use SoftDeletes;


    protected $perPage = 10;
    protected $table = 'ubigeo';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['descripcion', 'codigo', 'inei', 'tipo', 'ubigeo_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ubigeo()
    {
        return $this->belongsTo(\App\Models\Ubigeo::class, 'ubigeo_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sucursals()
    {
        return $this->hasMany(\App\Models\Sucursal::class, 'id', 'ubigeo_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ubigeos()
    {
        return $this->hasMany(\App\Models\Ubigeo::class, 'id', 'ubigeo_id');
    }
    

}
