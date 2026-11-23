<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DetMovhabitacion
 *
 * @property $id
 * @property $tipo_detalle
 * @property $movhabitacion_id
 * @property $codigo
 * @property $descripcion
 * @property $producto_id
 * @property $json_producto
 * @property $json_medidaproducto
 * @property $unidad_id
 * @property $json_unidad
 * @property $igv_id
 * @property $json_igv
 * @property $cantidad
 * @property $monto
 * @property $dcto_porcentaje
 * @property $monto_original
 * @property $comentario
 * @property $det_categoria
 * @property $opc_categoria
 * @property $det_movventa_id
 * @property $complementos
 * @property $situacion
 * @property $sucursal_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property DetMovventum $detMovventum
 * @property Igv $igv
 * @property Movhabitacion $movhabitacion
 * @property Producto $producto
 * @property Sucursal $sucursal
 * @property Unidad $unidad
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class DetMovhabitacion extends Model
{
    use SoftDeletes;




    protected $table = 'det_movhabitacion';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['tipo_detalle', 'movhabitacion_id', 'codigo', 'descripcion', 'producto_id', 'json_producto', 'json_medidaproducto', 'unidad_id', 'json_unidad', 'igv_id', 'json_igv', 'cantidad', 'monto', 'dcto_porcentaje', 'monto_original', 'comentario', 'det_categoria', 'opc_categoria', 'det_movventa_id', 'complementos', 'situacion', 'sucursal_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function detMovventum()
    {
        return $this->belongsTo(\App\Models\DetMovventum::class, 'det_movventa_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function igv()
    {
        return $this->belongsTo(\App\Models\Igv::class, 'igv_id', 'id');
    }
    
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
    public function producto()
    {
        return $this->belongsTo(\App\Models\Producto::class, 'producto_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unidad()
    {
        return $this->belongsTo(\App\Models\Unidad::class, 'unidad_id', 'id');
    }
    

}
