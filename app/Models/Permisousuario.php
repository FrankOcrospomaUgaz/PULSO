<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Permisousuario
 *
 * @property $situacion
 * @property $menuopcion_id
 * @property $perfil_id
 * @property $sucursal_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Menuopcione $menuopcione
 * @property Perfile $perfile
 * @property Sucursal $sucursal
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Permisousuario extends Model
{
    use SoftDeletes;
    protected $table = 'permisousuario';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['situacion', 'menuopcion_id', 'perfil_id', 'sucursal_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function menuopcione()
    {
        return $this->belongsTo(\App\Models\Menuopcione::class, 'menuopcion_id', 'id');
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
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id');
    }
}
