<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Anulacion
 *
 * @property $id
 * @property $movimiento_id
 * @property $usuario_id
 * @property $fecha
 * @property $motivo
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Movimiento $movimiento
 * @property User $user
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Anulacion extends Model
{
    use SoftDeletes;

    protected $table = 'anulacion';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['movimiento_id', 'usuario_id', 'fecha', 'motivo'];


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
    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_id', 'id');
    }
    

}
