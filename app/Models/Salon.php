<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Salon
 *
 * @property $id
 * @property $titulo
 * @property $descripcion_corta
 * @property $descripcion_larga
 * @property $imagen
 * @property $situacion
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Movreserva[] $movreservas
 * @property SalonPackage[] $salonPackages
 * @property SalonService[] $salonServices
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Salon extends Model
{
    use SoftDeletes;

    protected $table = 'salon';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['titulo', 'descripcion_corta', 'descripcion_larga', 'imagen', 'situacion', 'sucursal_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function movreservas()
    {
        return $this->hasMany(\App\Models\Movreserva::class, 'id', 'salon_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function paquetes()
    {
        return $this->hasMany(\App\Models\SalonPackage::class,  'salon_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function servicios()
    {
        return $this->hasMany(\App\Models\SalonService::class, 'salon_id');
    }
    

    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id');
    }

}
