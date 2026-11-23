<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CompromisoPago
 *
 * @property $id
 * @property $contrato_id
 * @property $personamaestro_id
 * @property $ambiente_id
 * @property $fecha_vencimiento
 * @property $fecha_pago
 * @property $numero_cuota
 * @property $monto
 * @property $forma_pago
 * @property $estado
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Ambiente $ambiente
 * @property Contrato $contrato
 * @property Personamaestro $personamaestro
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class CompromisoPago extends Model
{
    use SoftDeletes;


    protected $table = 'compromiso_pago';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['contrato_id', 'persona_id', 'ambiente_id', 'fecha_vencimiento', 'fecha_pago', 'numero_cuota', 'monto', 'forma_pago', 'estado', 'sucursal_id'];


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
    public function contrato()
    {
        return $this->belongsTo(\App\Models\Contrato::class, 'contrato_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function persona()
    {
        return $this->belongsTo(\App\Models\Persona::class, 'persona_id', 'id');
    }
    public function movventas()
{
    return $this->hasMany(\App\Models\Movventum::class, 'compromiso_pago_id', 'id')
        ->whereHas('movimiento', function ($query) {
            $query->where('situacion', '!=', 'D'); // Excluye los movventum cuyo movimiento.situacion es 'D'
        });
}

}
