<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class RelParametroempresa
 *
 * @property $valor
 * @property $parametro_id
 * @property $empresa_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Empresa $empresa
 * @property Parametro $parametro
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class RelParametroempresa extends Model
{
    use SoftDeletes;

    protected $table = 'rel_parametroempresa';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['valor', 'parametro_id', 'empresa_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function empresa()
    {
        return $this->belongsTo(\App\Models\Empresa::class, 'empresa_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parametro()
    {
        return $this->belongsTo(\App\Models\Parametro::class, 'parametro_id', 'id');
    }
    

}
