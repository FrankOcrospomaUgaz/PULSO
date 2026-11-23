<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Movimiento
 *
 * @property $id
 * @property $numero
 * @property $fecha
 * @property $usuario_id
 * @property $usuario
 * @property $persona_id
 * @property $persona
 * @property $responsable_id
 * @property $responsable
 * @property $comentario
 * @property $situacion
 * @property $tipomovimiento_id
 * @property $tipodocumento_id
 * @property $sucursal_id
 * @property $movimiento_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Movimiento $movimiento
 * @property Sucursal $sucursal
 * @property Tipodocumento $tipodocumento
 * @property Tipomovimiento $tipomovimiento
 * @property Movalmacen[] $movalmacens
 * @property Movimiento[] $movimientos
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Movimiento extends Model
{
    use SoftDeletes;


    protected $table = 'movimiento';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['numero', 'fecha', 'usuario_id', 'usuario', 'persona_id', 'persona', 'responsable_id', 'responsable', 'comentario', 'situacion', 'tipomovimiento_id', 'tipodocumento_id', 'sucursal_id', 'movimiento_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function movimiento()
    {
        return $this->belongsTo(\App\Models\Movimiento::class, 'movimiento_id', 'id');
    }
    
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
    public function tipodocumento()
    {
        return $this->belongsTo(\App\Models\Tipodocumento::class, 'tipodocumento_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tipomovimiento()
    {
        return $this->belongsTo(\App\Models\Tipomovimiento::class, 'tipomovimiento_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function movalmacens()
    {
        return $this->hasMany(\App\Models\Movalmacen::class, 'id', 'movimiento_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function movventa()
    {
        return $this->hasOne(\App\Models\Movventum::class, 'movimiento_id', 'id');
    }

    public function movcompra()
    {
        return $this->hasOne(\App\Models\Movcompra::class, 'movimiento_id', 'id');
    }

    public function movhabitacion()
    {
        return $this->hasOne(\App\Models\Movhabitacion::class, 'movimiento_id', 'id');
    }
        public function movreserva()
    {
        return $this->hasOne(\App\Models\Movreserva::class, 'movimiento_id', 'id');
    }
    public function movcaja()
    {
        return $this->hasOne(\App\Models\Movcaja::class, 'movimiento_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function movimientos()
    {
        return $this->hasMany(\App\Models\Movimiento::class, 'id', 'movimiento_id');
    }
    
    public function Responsable()
    {
        return $this->belongsTo(Persona::class, 'responsable_id');
    }

    public function Persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

        public function Usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function getSituacionAttribute($value)
    {
        return $value === 'A' ? 'Activado' : 'Desactivado';
    }

}
