<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Ambiente
 *
 * @property $id
 * @property $numero
 * @property $tipo
 * @property $monto
 * @property $garantia
 * @property $piso_id
 * @property $edificio_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Edificio $edificio
 * @property Piso $piso
 * @property CompromisoPago[] $compromisoPagos
 * @property Contrato[] $contratos
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Ambiente extends Model
{
    use SoftDeletes;


    protected $table = 'ambiente';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['numero', 'monto', 'garantia', 'piso_id', 'edificio_id', 'sucursal_id', 'tipoambiente_id', 'situacion'];
    public function tipoambiente()
    {
        return $this->belongsTo(\App\Models\Tipoambiente::class, 'tipoambiente_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function edificio()
    {
        return $this->belongsTo(\App\Models\Edificio::class, 'edificio_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function piso()
    {
        return $this->belongsTo(\App\Models\Piso::class, 'piso_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function compromisoPagos()
    {
        return $this->hasMany(\App\Models\CompromisoPago::class, 'id', 'ambiente_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contratos()
    {
        return $this->hasMany(\App\Models\Contrato::class, 'id', 'ambiente_id');
    }
    
public function getSituacionAttribute($value)
{
    return match ($value) {
        'L' => 'LIbre',
        'A' => 'Alquilado',
        'M' => 'Mantenimiento',
        default => 'LIbre',
    };
}
}
