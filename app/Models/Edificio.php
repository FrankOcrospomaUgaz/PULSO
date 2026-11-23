<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Edificio
 *
 * @property $id
 * @property $nombre
 * @property $direccion
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Ambiente[] $ambientes
 * @property Contrato[] $contratos
 * @property Piso[] $pisos
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Edificio extends Model
{
    use SoftDeletes;


    protected $table = 'edificio';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['nombre', 'direccion', 'sucursal_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ambientes()
    {
        return $this->hasMany(\App\Models\Ambiente::class, 'id', 'edificio_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contratos()
    {
        return $this->hasMany(\App\Models\Contrato::class, 'id', 'edificio_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pisos()
    {
        return $this->hasMany(\App\Models\Piso::class, 'id', 'edificio_id');
    }
    

}
