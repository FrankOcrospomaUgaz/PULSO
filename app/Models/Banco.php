<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Banco
 *
 * @property $id
 * @property $descripcion
 * @property $orden
 * @property $situacion
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Banco extends Model
{
    use SoftDeletes;

    protected $table = 'banco';

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
