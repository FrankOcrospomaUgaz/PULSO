<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Tipopreguntum
 *
 * @property $id
 * @property $nombre
 * @property $descripcion
 * @property $situacion
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property DetEncuestum[] $detEncuestas
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Tipopreguntum extends Model
{
    use SoftDeletes;

    protected $table = 'tipopregunta';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['nombre', 'descripcion', 'situacion'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detEncuestas()
    {
        return $this->hasMany(\App\Models\DetEncuestum::class, 'id', 'tipopregunta_id');
    }
    

}
