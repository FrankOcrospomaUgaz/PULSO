<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Habitacion
 *
 * @property $id
 * @property $numero
 * @property $situacion
 * @property $piso_id
 * @property $tipohabitacion_id
 * @property $created_at
 * @property $updated_at
 *
 * @property Piso $piso
 * @property Tipohabitacion $tipohabitacion
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Habitacion extends Model
{
    use SoftDeletes;

    protected $table = 'habitacion';
    protected $perPage = 10;
    protected $dates = ['deleted_at'];
    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['numero', 'situacion', 'piso_id', 'tipohabitacion_id', 'sucursal_id', 'descripcion', 'precio'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function piso()
    {
        return $this->belongsTo(\App\Models\Piso::class, 'piso_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tipohabitacion()
    {
        return $this->belongsTo(\App\Models\Tipohabitacion::class, 'tipohabitacion_id', 'id');
    }

    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id');
    }
    

}
