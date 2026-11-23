<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * Class Producto
 *
 * @property $id
 * @property $codigo
 * @property $descripcion
 * @property $abreviatura
 * @property $tipo
 * @property $categoria_id
 * @property $marca_id
 * @property $unidadbase_id
 * @property $medidapeso_id
 * @property $kardex
 * @property $compuesto
 * @property $autoproduccion
 * @property $peso
 * @property $comentario
 * @property $imagen
 * @property $complemento
 * @property $modocomplemento
 * @property $empresa_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 * @property $clasificacion
 * @property $nro_parte
 * @property $caracteristicas
 * @property $distribuidor_id
 * @property $imagen2
 * @property $imagen3
 *
 * @property Categorium $categorium
 * @property Distribuidor $distribuidor
 * @property Empresa $empresa
 * @property Marca $marca
 * @property Unidad $unidad
 * @property Unidad $unidad
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Producto extends Model
{
    use SoftDeletes;


    protected $perPage = 10;
    protected $table = 'producto';


    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['codigo', 'descripcion', 'abreviatura', 'tipo', 'categoria_id', 'marca_id', 'unidadbase_id', 'medidapeso_id', 'kardex', 'compuesto', 'autoproduccion', 'peso', 'comentario', 'imagen', 'complemento', 'modocomplemento', 'empresa_id', 'clasificacion', 'nro_parte', 'caracteristicas', 'distribuidor_id', 'imagen2', 'imagen3'];

    protected $attributes = [
        'imagen' => ' ',

    ];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function categorium()
    {
        return $this->belongsTo(\App\Models\Categorium::class, 'categoria_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function distribuidor()
    {
        return $this->belongsTo(\App\Models\Distribuidor::class, 'distribuidor_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function empresa()
    {
        return $this->belongsTo(\App\Models\Empresa::class, 'empresa_id', 'id');
    }
    public function medidapeso()
    {
        return $this->belongsTo(\App\Models\Unidad::class, 'medidapeso_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function marca()
    {
        return $this->belongsTo(\App\Models\Marca::class, 'marca_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unidad()
    {
        return $this->belongsTo(\App\Models\Unidad::class, 'unidadbase_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function relProductosucursal()
    {
        return $this->hasOne(\App\Models\RelProductosucursal::class, 'producto_id', 'id');
    }
    /**
 * Relación con la tabla Stockproducto
 */
/**
 * Relación con la tabla Stockproducto
 */
public function stockproductos()
{
    return $this->hasMany(\App\Models\Stockproducto::class, 'producto_id', 'id');
}



    // En Producto.php
    public function relListaUnidadSucursal()
    {
        return $this->hasOneThrough(
            \App\Models\RelListaunidadsucursal::class,
            \App\Models\Listaunidad::class,
            'producto_id',   // Foreign key on Listaunidad table
            'listaunidad_id', // Foreign key on RelListaunidadsucursal table
            'id',            // Local key on Producto table
            'id'             // Local key on Listaunidad table
        );
    }


}
