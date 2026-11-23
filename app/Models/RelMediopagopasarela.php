<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class RelMediopagopasarela
 *
 * @property $id
 * @property $pasarela_id
 * @property $medio_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Mediopago $mediopago
 * @property Pasarelapago $pasarelapago
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class RelMediopagopasarela extends Model
{
    use SoftDeletes;
    protected $table = 'rel_mediopagopasarela';


    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['pasarela_id', 'medio_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mediopago()
    {
        return $this->belongsTo(\App\Models\Mediopago::class, 'medio_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pasarelapago()
    {
        return $this->belongsTo(\App\Models\Pasarelapago::class, 'pasarela_id', 'id');
    }
    

}
