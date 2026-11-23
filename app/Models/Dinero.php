<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Dinero
 *
 * @property $id
 * @property $valor
 * @property $tipo
 * @property $descripcion
 * @property $orden
 * @property $situacion
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property RelMovcajadinero[] $relMovcajadineros
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Dinero extends Model
{
    use SoftDeletes;


    protected $table = 'dinero';


    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['valor', 'tipo', 'descripcion', 'orden', 'situacion'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function relMovcajadineros()
    {
        return $this->hasMany(\App\Models\RelMovcajadinero::class, 'id', 'dinero_id');
    }
    

}
