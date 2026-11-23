<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ImagenesTipohabitacion
 *
 * @property $id
 * @property $tipohabitacion_id
 * @property $url
 * @property $descripcion
 * @property $created_at
 * @property $updated_at
 *
 * @property Tipohabitacion $tipohabitacion
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class ImagenesTipohabitacion extends Model
{
    


    protected $table = 'imagenes_tipohabitacion';

    protected $perPage = 10;
    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['tipohabitacion_id', 'url', 'descripcion'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tipohabitacion()
    {
        return $this->belongsTo(\App\Models\Tipohabitacion::class, 'tipohabitacion_id', 'id');
    }
    

}
