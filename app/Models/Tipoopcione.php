<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Tipoopcione
 *
 * @property $id
 * @property $descripcion
 * @property $abreviatura
 * @property $orden
 * @property $icono
 * @property $estado
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Tipoopcione extends Model
{
    use SoftDeletes;

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['descripcion', 'abreviatura', 'orden', 'icono', 'estado'];

    protected $attributes = [
        'estado' => true,
        'icono' => ''
    ];

    /**
     * Get the estado as a human-readable string.
     *
     * @return string
     */
    public function getEstadoAttribute($value)
    {
        return $value ? 'Normal' : 'Eliminado';
    }
}
