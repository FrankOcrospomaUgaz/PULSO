<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class RelListaunidadsucursal
 *
 * @property $id
 * @property $preciocompra
 * @property $preciomanoobra
 * @property $precioventa
 * @property $moneda
 * @property $listaunidad_id
 * @property $sucursal_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Listaunidad $listaunidad
 * @property Sucursal $sucursal
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class RelListaunidadsucursal extends Model
{
    use SoftDeletes;



    protected $table = 'rel_listaunidadsucursal';

    protected $perPage = 10;


    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['preciocompra', 'preciomanoobra', 'precioventa', 'moneda', 'listaunidad_id', 'sucursal_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function listaunidad()
    {
        return $this->belongsTo(\App\Models\Listaunidad::class, 'listaunidad_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id');
    }

    
    

}
