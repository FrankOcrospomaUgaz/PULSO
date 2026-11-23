<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Tipoambiente
 *
 * @property $id
 * @property $nombre
 * @property $descripcion
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Ambiente[] $ambientes
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Tipoambiente extends Model
{
    use SoftDeletes;

    protected $table = 'tipoambiente';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['nombre', 'descripcion'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ambientes()
    {
        return $this->hasMany(\App\Models\Ambiente::class, 'id', 'tipoambiente_id');
    }
      public function plantillaContrato()
    {
        return $this->hasOne(\App\Models\PlantillaContrato::class, 'tipo_ambiente_id', 'id');
    }

}
