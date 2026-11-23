<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Tarjetum
 *
 * @property $id
 * @property $descripcion
 * @property $tipo
 * @property $orden
 * @property $situacion
 * @property $icono
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Tarjetum extends Model
{
    use SoftDeletes;

    protected $table = 'tarjeta';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['descripcion', 'tipo', 'orden', 'situacion', 'icono'];


    public function getTipoAttribute($value)
    {
        return $value === 'C' ? 'Crédito' : 'Débito';
    }

    public function getSituacionAttribute($value)
    {
        return $value ? 'Activado' : 'Desactivado';
    }


}
