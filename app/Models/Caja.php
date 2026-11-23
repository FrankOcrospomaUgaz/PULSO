<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Caja
 *
 * @property $id
 * @property $numero
 * @property $situacion
 * @property $serie
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Movcaja[] $movcajas
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Caja extends Model
{
    use SoftDeletes;



    protected $table = 'caja';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['numero', 'situacion', 'serie', 'sucursal_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function movcajas()
    {
        return $this->hasMany(\App\Models\Movcaja::class, 'id', 'caja_id');
    }
    

}
