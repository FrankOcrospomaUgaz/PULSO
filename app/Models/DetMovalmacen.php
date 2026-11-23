<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DetMovalmacen
 *
 * @property $id
 * @property $movalmacen_id
 * @property $producto_id
 * @property $json_producto
 * @property $unidad_id
 * @property $json_listaunidad
 * @property $cantidad
 * @property $comentario
 * @property $situacion
 * @property $sucursal_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Movalmacen $movalmacen
 * @property Sucursal $sucursal
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class DetMovalmacen extends Model
{
    use SoftDeletes;



    protected $table = 'det_movalmacen';

    protected $perPage = 10;


    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['movalmacen_id', 'producto_id', 'json_producto', 'unidad_id', 'json_listaunidad', 'cantidad', 'comentario', 'situacion', 'sucursal_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function movalmacen()
    {
        return $this->belongsTo(\App\Models\Movalmacen::class, 'movalmacen_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id');
    }

    public function producto()
    {
        return $this->belongsTo(\App\Models\Producto::class, 'producto_id', 'id');
    }
    
    public function unidad()
    {
        return $this->belongsTo(\App\Models\Unidad::class, 'unidad_id', 'id');
    }
    

}
