<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Persona
 *
 * @property $id
 * @property $nombres
 * @property $apellidos
 * @property $direccion
 * @property $email
 * @property $telefonofijo
 * @property $telefonomovil
 * @property $imagen
 * @property $compartido
 * @property $personamaestro_id
 * @property $sucursal_id
 * @property $distrito_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Ubigeo $ubigeo
 * @property Personamaestro $personamaestro
 * @property Sucursal $sucursal
 * @property Rolpersona[] $rolpersonas
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class   Persona extends Model
{
    use SoftDeletes;
    protected $table = 'persona';


    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['nombres', 'apellidos', 'direccion', 'email', 'telefonofijo', 'telefonomovil', 'imagen', 'compartido', 'personamaestro_id', 'sucursal_id', 'distrito_id', 'no_grato'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ubigeo()
    {
        return $this->belongsTo(\App\Models\Ubigeo::class, 'distrito_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function personamaestro()
    {
        return $this->belongsTo(\App\Models\Personamaestro::class, 'personamaestro_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getCompartidoAttribute($value)
    {
        return $value === 'S' ? 'Sí' : 'No';
    }

    public function rolpersonas()
    {
        return $this->hasMany(Rolpersona::class, 'persona_id', 'id');
    }

    public function usuario()
    {
        return $this->hasOne(User::class, 'persona_id', 'id');
    }
public function huespedes()
{
    return $this->hasMany(\App\Models\Huesped::class, 'persona_id', 'id');
}


}
