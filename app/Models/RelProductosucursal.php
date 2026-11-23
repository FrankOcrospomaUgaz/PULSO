<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class RelProductosucursal
 *
 * @property $id
 * @property $situacion
 * @property $fechavencimiento
 * @property $stockminimo
 * @property $stockmaximo
 * @property $stockoptimo
 * @property $minimovender
 * @property $minimocomprar
 * @property $ubicacion_id
 * @property $fila
 * @property $columna
 * @property $favorito
 * @property $subalmacen_id
 * @property $igv_id
 * @property $venta_unitaria
 * @property $producto_id
 * @property $sucursal_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 * @property $duracion
 * @property $proveedor_id
 *
 * @property Igv $igv
 * @property Producto $producto
 * @property Persona $persona
 * @property Subalmacen $subalmacen
 * @property Sucursal $sucursal
 * @property Ubicacion $ubicacion
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class RelProductosucursal extends Model
{
    use SoftDeletes;


    protected $perPage = 10;
    protected $table = 'rel_productosucursal';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['situacion', 'fechavencimiento', 'stockminimo', 'stockmaximo', 'stockoptimo', 'minimovender', 'minimocomprar', 'ubicacion_id', 'fila', 'columna', 'favorito', 'subalmacen_id', 'igv_id', 'venta_unitaria', 'producto_id', 'sucursal_id', 'duracion', 'proveedor_id'];


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
    public function producto()
    {
        return $this->belongsTo(\App\Models\Producto::class, 'producto_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function persona()
    {
        return $this->belongsTo(\App\Models\Persona::class, 'proveedor_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subalmacen()
    {
        return $this->belongsTo(\App\Models\Subalmacen::class, 'subalmacen_id', 'id');
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
    public function ubicacion()
    {
        return $this->belongsTo(\App\Models\Ubicacion::class, 'ubicacion_id', 'id');
    }
    public function getSituacionAttribute($value)
    {
        return $value === 'A' ? 'Activado' : 'Desactivado';
    }

}
