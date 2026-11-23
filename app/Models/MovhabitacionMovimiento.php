<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MovhabitacionMovimiento
 *
 * @property $id
 * @property $movhabitacion_id
 * @property $movimiento_id
 * @property $dias_pagados
 * @property $horaspagadas
 * @property $created_at
 * @property $updated_at
 *
 * @property Movhabitacion $movhabitacion
 * @property Movimiento $movimiento
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class MovhabitacionMovimiento extends Model
{
    


    protected $table = 'movhabitacion_movimiento';

    protected $perPage = 10;


    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['movhabitacion_id', 'movimiento_id', 'dias_pagados', 'horaspagadas'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function movhabitacion()
    {
        return $this->belongsTo(\App\Models\Movhabitacion::class, 'movhabitacion_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function movimiento()
    {
        return $this->belongsTo(\App\Models\Movimiento::class, 'movimiento_id', 'id');
    }
    

}
