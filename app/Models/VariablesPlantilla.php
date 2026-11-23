<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class VariablesPlantilla
 *
 * @property $id
 * @property $codigo
 * @property $situacion
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class VariablesPlantilla extends Model
{
    use SoftDeletes;


    protected $table = 'variables_plantilla';

    protected $perPage = 10;


    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['codigo', 'situacion'];



}
