<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Motivoreserva
 *
 * @property $id
 * @property $codigo
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
class Motivoreserva extends Model
{
    use SoftDeletes;



    protected $table = 'motivoreserva';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['codigo', 'descripcion', 'orden', 'situacion'];



}
