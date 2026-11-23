<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Rol
 *
 * @property $id
 * @property $descripcion
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Rolpersona[] $rolpersonas
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Rol extends Model
{
    use SoftDeletes;

    protected $table = 'rol';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['descripcion'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rolpersonas()
    {
        return $this->hasMany(\App\Models\Rolpersona::class, 'id', 'rol_id');
    }
    

}
