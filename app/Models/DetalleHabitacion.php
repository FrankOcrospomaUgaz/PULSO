<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * Class DetalleHabitacion
 *
 * @property $id
 * @property $habitacion_id
 * @property $descripcion
 * @property $icono
 * @property $situacion
 * @property $created_at
 * @property $updated_at
 *
 * @property Habitacion $habitacion
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class DetalleHabitacion extends Model
{
    
    use SoftDeletes;



    protected $table = 'detalle_habitacion';

    protected $perPage = 10;
    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['habitacion_id', 'descripcion', 'icono', 'situacion'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function habitacion()
    {
        return $this->belongsTo(\App\Models\Habitacion::class, 'habitacion_id', 'id');
    }
    

}
