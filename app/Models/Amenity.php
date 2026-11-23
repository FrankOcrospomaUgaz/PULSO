<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * Class Amenity
 *
 * @property $id
 * @property $nombre
 * @property $icon
 * @property $tipohabitacion_id
 * @property $created_at
 * @property $updated_at
 *
 * @property Tipohabitacion $tipohabitacion
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Amenity extends Model
{
    use SoftDeletes;


    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['nombre', 'icon', 'tipohabitacion_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tipohabitacion()
    {
        return $this->belongsTo(\App\Models\Tipohabitacion::class, 'tipohabitacion_id', 'id');
    }
    

}
