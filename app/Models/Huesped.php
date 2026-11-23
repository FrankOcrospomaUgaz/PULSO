<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Huesped
 *
 * @property $id
 * @property $persona_id
 * @property $movhabitacion_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Movhabitacion $movhabitacion
 * @property Persona $persona
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Huesped extends Model
{
    use SoftDeletes;

    protected $table = 'huesped';


    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['persona_id', 'movhabitacion_id', 'habitacion_id', 'movreserva_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function movhabitacion()
    {
        return $this->belongsTo(\App\Models\Movhabitacion::class, 'movhabitacion_id', 'id');
    }
    public function movreserva()
    {
        return $this->belongsTo(\App\Models\Movreserva::class, 'movreserva_id', 'id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function persona()
    {
        return $this->belongsTo(\App\Models\Persona::class, 'persona_id', 'id');
    }
    public function habitacion()
    {
        return $this->belongsTo(\App\Models\Habitacion::class, 'habitacion_id', 'id');
    }

}
