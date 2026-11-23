<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Motivomovimiento
 *
 * @property $id
 * @property $codigo
 * @property $tipomovimiento_id
 * @property $orden
 * @property $situacion
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Tipomovimiento $tipomovimiento
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Motivomovimiento extends Model
{
    use SoftDeletes;

    protected $table = 'motivomovimiento';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['codigo', 'descripcion', 'tipomovimiento_id', 'orden', 'situacion'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tipomovimiento()
    {
        return $this->belongsTo(\App\Models\Tipomovimiento::class, 'tipomovimiento_id', 'id');
    }
    

}
