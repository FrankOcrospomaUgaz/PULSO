<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Movcaja extends Model
{
    use SoftDeletes;

    protected $table = 'movcaja';
    protected $perPage = 10;

    protected $fillable = [
        'conceptopago_id', 'moneda', 'tipocambio', 'total',
        'caja_id', 'caja', 'turno_id', 'json_turno',
        'movimiento_id', 'sucursal_id'
    ];

    /** Relaciones principales */
    public function caja()
    {
        return $this->belongsTo(\App\Models\Caja::class, 'caja_id', 'id');
    }

    public function conceptopago()
    {
        return $this->belongsTo(\App\Models\Conceptopago::class, 'conceptopago_id', 'id');
    }

    public function movimiento()
    {
        return $this->belongsTo(\App\Models\Movimiento::class, 'movimiento_id', 'id');
    }

    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id');
    }

    public function turno()
    {
        return $this->belongsTo(\App\Models\Turno::class, 'turno_id', 'id');
    }

    public function detMovcaja()
    {
        return $this->hasMany(\App\Models\DetMovcaja::class, 'movcaja_id', 'id');
    }

    /**
     * 🔗 Obtener el movventa relacionado con esta movcaja
     * Primero busca si el movventa tiene el mismo movimiento_id.
     * Si no lo encuentra, busca el movventa cuyo movimiento apunte al movimiento de esta caja.
     */
public function movventa()
{
    return $this->hasOneThrough(
        \App\Models\Movventum::class,   // Modelo final (venta)
        \App\Models\Movimiento::class,  // Modelo intermedio
        'id',                           // Clave primaria en movimiento (intermedio)
        'movimiento_id',                // Clave foránea en movventa
        'movimiento_id',                // Clave local en movcaja
        'movimiento_id'                 // Clave en movimiento que apunta al padre (venta)
    );
}

}
