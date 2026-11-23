<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Mediopago
 *
 * @property $id
 * @property $descripcion
 * @property $orden
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Mediopago extends Model
{
    use SoftDeletes;

    protected $table = 'mediopago';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['descripcion', 'orden', 'situacion'];

    public function getSituacionAttribute($value)
    {
        return $value ? 'Activado' : 'Desactivado';
    }


}
