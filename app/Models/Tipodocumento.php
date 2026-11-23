<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Tipodocumento
 *
 * @property $id
 * @property $nombre
 * @property $abreviatura
 * @property $stock
 * @property $tipomovimiento_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Tipomovimiento $tipomovimiento
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Tipodocumento extends Model
{
    use SoftDeletes;


    protected $table = 'tipodocumento';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['nombre', 'abreviatura', 'stock', 'tipomovimiento_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tipomovimiento()
    {
        return $this->belongsTo(\App\Models\Tipomovimiento::class, 'tipomovimiento_id', 'id');
    }
    

}
