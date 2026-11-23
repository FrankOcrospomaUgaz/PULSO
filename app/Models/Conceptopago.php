<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Conceptopago
 *
 * @property $id
 * @property $descripcion
 * @property $tipo
 * @property $restringido
 * @property $comentarios
 * @property $created_at
 * @property $updated_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Conceptopago extends Model
{
    
    protected $table = 'conceptopago';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['descripcion', 'tipo', 'restringido'];



    public function getTipoAttribute($value)
    {
        return $value === 'I' ? 'Ingreso' : 'Egreso';
    }

    public function getRestringidoAttribute($value)
    {
        return $value ? 'Sí' : 'No';
    }


}
