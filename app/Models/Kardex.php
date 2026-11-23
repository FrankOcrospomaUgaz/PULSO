<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Kardex
 *
 * @property $id
 * @property $detalle_id
 * @property $producto_id
 * @property $json_producto
 * @property $unidad_id
 * @property $json_listaunidad
 * @property $cantidad
 * @property $unidadbase_id
 * @property $cantidadbase
 * @property $preciounitario
 * @property $moneda
 * @property $tipocambio
 * @property $total
 * @property $fecha
 * @property $situacion
 * @property $usuario_id
 * @property $usuario
 * @property $movimiento_id
 * @property $tipomovimiento_id
 * @property $tipodocumento_id
 * @property $sucursal_id
 * @property $stockanterior
 * @property $stockactual
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property DetMovalmacen $detMovalmacen
 * @property Movimiento $movimiento
 * @property Producto $producto
 * @property Sucursal $sucursal
 * @property Tipodocumento $tipodocumento
 * @property Tipomovimiento $tipomovimiento
 * @property Unidad $unidad
 * @property Unidad $unidad
 * @property User $user
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Kardex extends Model
{
    use SoftDeletes;


    protected $table = 'kardex';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['detalle_id', 'producto_id', 'json_producto', 'unidad_id', 'json_listaunidad', 'cantidad', 'unidadbase_id', 'cantidadbase', 'preciounitario', 'moneda', 'tipocambio', 'total', 'fecha', 'situacion', 'usuario_id', 'usuario', 'movimiento_id', 'tipomovimiento_id', 'tipodocumento_id', 'sucursal_id', 'stockanterior', 'stockactual'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function detMovalmacen()
    {
        return $this->belongsTo(\App\Models\DetMovalmacen::class, 'detalle_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function movimiento()
    {
        return $this->belongsTo(\App\Models\Movimiento::class, 'movimiento_id', 'id');
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
    public function tipodocumento()
    {
        return $this->belongsTo(\App\Models\Tipodocumento::class, 'tipodocumento_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tipomovimiento()
    {
        return $this->belongsTo(\App\Models\Tipomovimiento::class, 'tipomovimiento_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unidad()
    {
        return $this->belongsTo(\App\Models\Unidad::class, 'unidad_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unidadbase()
    {
        return $this->belongsTo(\App\Models\Unidad::class, 'unidadbase_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_id', 'id');
    }
    
    public function getSituacionAttribute($value)
    {
        return $value === 'E' ? 'Activado' : 'Desactivado';
    }

}
