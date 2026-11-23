<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class RelTablasucursal
 *
 * @property $id
 * @property $tabla_id
 * @property $sucursal_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Sucursal $sucursal
 * @property Tabla $tabla
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class RelTablasucursal extends Model
{
    use SoftDeletes;

    protected $table = 'rel_tablasucursal';

    protected $perPage = 0;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['tabla_id', 'sucursal_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tabla()
    {
        return $this->belongsTo(\App\Models\Tabla::class, 'tabla_id', 'id');
    }
    

}
