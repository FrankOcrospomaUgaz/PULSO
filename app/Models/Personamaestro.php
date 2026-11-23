<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Personamaestro
 *
 * @property $id
 * @property $tipopersona
 * @property $nombres
 * @property $apellidos
 * @property $nrodoc
 * @property $sexo
 * @property $fechanac
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Persona[] $personas
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Personamaestro extends Model
{
    use SoftDeletes;

    protected $table = 'personamaestro';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['tipopersona', 'nombres', 'apellidos', 'nrodoc', 'sexo', 'fechanac'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function personas()
    {
        return $this->hasMany(\App\Models\Persona::class, 'id', 'personamaestro_id');
    }
    


    public function getSexoAttribute($value)
    {
        if ($value === 'M') {
            return 'Masculino';
        } elseif ($value === 'F') {
            return 'Femenino';
        } else {
            return ''; // Retorna 'N' si no es ni 'M' ni 'F'
        }
    }
    
}
