<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Categorium
 *
 * @property $id
 * @property $descripcion
 * @property $abreviatura
 * @property $nivel
 * @property $orden
 * @property $padre
 * @property $imagen
 * @property $categoria_id
 * @property $empresa_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Categorium $categorium
 * @property Empresa $empresa
 * @property Categorium[] $categorias
 * @property Producto[] $productos
 * @property RelCategoriasucursal[] $relCategoriasucursals
 * @package App 
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Categorium extends Model
{
    use SoftDeletes;

    protected $table = 'categoria';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['descripcion', 'abreviatura', 'nivel', 'orden', 'padre', 'imagen', 'categoria_id', 'empresa_id'];
    protected $attributes = [
        'imagen' => ' ',

    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function empresa()
    {
        return $this->belongsTo(\App\Models\Empresa::class, 'empresa_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function categorias()
    {
        return $this->hasMany(\App\Models\Categorium::class, 'id', 'categoria_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productos()
    {
        return $this->hasMany(\App\Models\Producto::class, 'id', 'producto_id');
    }
    public function relCategoriasucursal()
    {
        return $this->hasOne(\App\Models\RelCategoriasucursal::class, 'categoria_id', 'id');
    }


}
