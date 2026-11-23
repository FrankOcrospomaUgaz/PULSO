<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Igv
 *
 * @property $id
 * @property $codigo
 * @property $descripcion
 * @property $porcentaje_igv
 * @property $orden
 * @property $situacion
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Igv extends Model
{
    use SoftDeletes;

    protected $table = 'igv';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['codigo', 'descripcion', 'porcentaje_igv', 'orden', 'situacion'];



}
