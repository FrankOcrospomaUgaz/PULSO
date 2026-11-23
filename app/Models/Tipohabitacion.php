<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Tipohabitacion
 *
 * @property $id
 * @property $nombre
 * @property $capacidad
 * @property $precio
 * @property $estado
 * @property $created_at
 * @property $updated_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Tipohabitacion extends Model
{
    use SoftDeletes;

    protected $table = 'tipohabitacion';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['nombre', 'capacidad', 'precio', 'estado', 'sucursal_id', 'json_precio_horas', 'descripcion'];

    public function getEstadoAttribute($value)
    {
        return $value ? 'Normal' : 'Eliminado';
    }
    public function imagenes()
    {
        return $this->hasMany(ImagenesTipohabitacion::class);
    }

    public function habitaciones()
    {
        return $this->hasMany(Habitacion::class);
    }
    public function amenities()
    {
        return $this->hasMany(Amenity::class, 'tipohabitacion_id');
    }
}
