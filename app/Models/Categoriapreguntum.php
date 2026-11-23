<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Categoriapreguntum
 *
 * @property $id
 * @property $nombre
 * @property $situacion
 * @property $sucursal_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Sucursal $sucursal
 * @property DetEncuestum[] $detEncuestas
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Categoriapreguntum extends Model
{
    use SoftDeletes;


    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['nombre', 'situacion', 'sucursal_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detEncuestas()
    {
        return $this->hasMany(\App\Models\DetEncuestum::class, 'id', 'categoriapregunta_id');
    }
    
    public function getSituacionAttribute($value)
    {
        return $value === 'E' ? 'Activado' : 'Desactivado';
    }

}
