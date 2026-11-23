<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class RelMovcajadinero
 *
 * @property $cantidad
 * @property $comentario
 * @property $dinero_id
 * @property $movcaja_id
 * @property $sucursal_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Dinero $dinero
 * @property Movcaja $movcaja
 * @property Sucursal $sucursal
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class RelMovcajadinero extends Model
{
    use SoftDeletes;

    protected $table = 'rel_movcajadinero';

    // No hay primary key autoincremental
    protected $primaryKey = null;
    public $incrementing = false;
    protected $keyType = 'string'; // O 'int', pero como no hay PK, da igual

    protected $fillable = [
        'cantidad',
        'comentario',
        'dinero_id',
        'movcaja_id',
        'sucursal_id'
    ];

    public function dinero()
    {
        return $this->belongsTo(Dinero::class, 'dinero_id', 'id');
    }

    public function movcaja()
    {
        return $this->belongsTo(Movcaja::class, 'movcaja_id', 'id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id');
    }
}
