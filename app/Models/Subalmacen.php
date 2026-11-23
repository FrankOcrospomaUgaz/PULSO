<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Subalmacen
 *
 * @property $id
 * @property $nombre
 * @property $comentario
 * @property $situacion
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property RelProductosucursal[] $relProductosucursals
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Subalmacen extends Model
{
    use SoftDeletes;

    protected $table = 'subalmacen';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['nombre', 'comentario', 'situacion'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function relProductosucursals()
    {
        return $this->hasMany(\App\Models\RelProductosucursal::class, 'id', 'subalmacen_id');
    }
    

}
