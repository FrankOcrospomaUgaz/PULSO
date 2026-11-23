<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Contrato
 *
 * @property $id
 * @property $numero_contrato
 * @property $fecha_inicio
 * @property $fecha_fin
 * @property $dia_pago
 * @property $monto
 * @property $garantia
 * @property $cubre_luz
 * @property $cubre_agua
 * @property $descripcion
 * @property $informacion_adicional
 * @property $ambiente_id
 * @property $personamaestro_id
 * @property $edificio_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Ambiente $ambiente
 * @property Edificio $edificio
 * @property Personamaestro $personamaestro
 * @property CompromisoPago[] $compromisoPagos
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Contrato extends Model
{
    use SoftDeletes;


    protected $table = 'contrato';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['numero_contrato', 'fecha_inicio', 'fecha_fin', 'dia_pago', 'monto', 'garantia', 'cubre_luz', 'cubre_agua', 'descripcion', 'informacion_adicional', 'ambiente_id', 'persona_id','arrendador_id', 'edificio_id', 'sucursal_id', 'pdfcontrato', 'situacion'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ambiente()
    {
        return $this->belongsTo(\App\Models\Ambiente::class, 'ambiente_id', 'id');
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
    public function arrendatario()
    {
        return $this->belongsTo(\App\Models\Persona::class, 'persona_id', 'id');
    }
        public function arrendador()
    {
        return $this->belongsTo(\App\Models\Persona::class, 'arrendador_id', 'id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function compromisoPagos()
    {
        return $this->hasMany(\App\Models\CompromisoPago::class, 'id', 'contrato_id');
    }
    
public function getSituacionAttribute($value)
{
    return match ($value) {
        'A' => 'Activo',
        'V' => 'Vencido',
        'R' => 'Resuelto',
        'C' => 'Cancelado',
        'S' => 'Suspendido temporalmente',
        default => 'Desconocido',
    };
}

}
