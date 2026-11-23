<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Listaunidad
 *
 * @property $id
 * @property $formula
 * @property $producto_id
 * @property $unidad_id
 * @property $unidadbase_id
 * @property $empresa_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Empresa $empresa
 * @property Producto $producto
 * @property Unidad $unidad
 * @property Unidad $unidad
 * @property RelListaunidadsucursal[] $relListaunidadsucursals
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Listaunidad extends Model
{
    use SoftDeletes;


    protected $perPage = 10;
    protected $table = 'listaunidad';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['formula', 'producto_id', 'unidad_id', 'unidadbase_id', 'empresa_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function empresa()
    {
        return $this->belongsTo(\App\Models\Empresa::class, 'empresa_id', 'id');
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
    public function unidad()
    {
        return $this->belongsTo(\App\Models\Unidad::class, 'unidad_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unidadBase()
    {
        return $this->belongsTo(\App\Models\Unidad::class, 'unidadbase_id', 'id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function relListaunidadsucursals()
    {
        return $this->hasMany(\App\Models\RelListaunidadsucursal::class, 'id', 'listaunidad_id');
    }
    

}
