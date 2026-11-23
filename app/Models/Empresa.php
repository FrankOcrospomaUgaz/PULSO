<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Empresa
 *
 * @property $id
 * @property $razon_social
 * @property $direccion
 * @property $ruc
 * @property $email
 * @property $telefono_fijo
 * @property $telefono_movil
 * @property $logo
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Empresa extends Model
{
    use SoftDeletes;


    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['razon_social', 'direccion', 'ruc', 'email', 'telefono_fijo', 'telefono_movil', 'logo'];

    protected $attributes = [
        'telefono_fijo' => '',
        'telefono_movil' => '',
        'logo' => '',
];  
public function operaciones()
{
    return $this->hasManyThrough(Operacione::class, Tabla::class, 'id', 'tabla_id', 'tabla_id', 'id');
}
}
