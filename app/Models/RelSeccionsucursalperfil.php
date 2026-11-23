<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class RelSeccionsucursalperfil
 *
 * @property $perfil_id
 * @property $sucursal_id
 * @property $seccion_id
 * @property $menuopcionesdefecto_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Menuopcione $menuopcione
 * @property Perfile $perfile
 * @property Seccione $seccione
 * @property Sucursal $sucursal
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class RelSeccionsucursalperfil extends Model
{
    use SoftDeletes;

    protected $table = 'rel_seccionsucursalperfil';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['perfil_id', 'sucursal_id', 'seccion_id', 'menuopcionesdefecto_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function menuopcione()
    {
        return $this->belongsTo(\App\Models\Menuopcione::class, 'menuopcionesdefecto_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function perfile()
    {
        return $this->belongsTo(\App\Models\Perfile::class, 'perfil_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function seccione()
    {
        return $this->belongsTo(\App\Models\Seccione::class, 'seccion_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id');
    }
    

}
