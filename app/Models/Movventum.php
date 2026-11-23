<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Movventum
 *
 * @property $id
 * @property $json_sucursal
 * @property $serie
 * @property $anio
 * @property $tipo_detalle
 * @property $consumo
 * @property $tipo_pago
 * @property $estado
 * @property $tipo_venta
 * @property $moneda
 * @property $tipocambio
 * @property $subtotal
 * @property $igv
 * @property $total
 * @property $movimiento_id
 * @property $sucursal_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Movimiento $movimiento
 * @property Sucursal $sucursal
 * @property DetMovventum[] $detMovventas
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Movventum extends Model
{
    use SoftDeletes;

    protected $table = 'movventa';

    protected $perPage = 10;

    protected $fillable = ['json_sucursal', 'serie', 'anio', 'tipo_detalle', 'consumo', 'tipo_pago', 'estado', 'tipo_venta', 'moneda', 'tipocambio', 'subtotal', 'igv', 'total', 'movimiento_id', 'sucursal_id', 'tipo_cobro', 'movearlylate_id', 'movhabitacion_id', 'movreserva_id', 'compromiso_pago_id', 'detraccion', 'porcentaje_detraccion', 'cuenta_detraccion', 'codigo_bien_detraccion'];

    // Relación con el modelo Movimiento
    public function movimiento()
    {
        return $this->belongsTo(\App\Models\Movimiento::class, 'movimiento_id', 'id');
    }

    // Relación con el modelo Sucursal
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id');
    }

    // Relación con DetMovventum
    public function detMovventas()
    {
        return $this->hasMany(\App\Models\DetMovventum::class, 'movventa_id', 'id');
    }

    // Relación con Movhabitacion
    public function movhabitacion()
    {
        return $this->belongsTo(\App\Models\Movhabitacion::class, 'movhabitacion_id', 'id');
    }

public function movcaja()
{
    return $this->hasOneThrough(
        \App\Models\Movcaja::class,     // Modelo final
        \App\Models\Movimiento::class,  // Modelo intermedio
        'movimiento_id',                // Foreign key en movimiento (apunta al movimiento padre de venta)
        'movimiento_id',                // Foreign key en movcaja (apunta al movimiento hijo)
        'movimiento_id',                // Local key en movventa (su movimiento principal)
        'id'                            // Local key en movimiento (su ID)
    );
}

    public function compromisoPago()
    {
        return $this->belongsTo(\App\Models\CompromisoPago::class, 'compromiso_pago_id', 'id');
    }


    // Métodos de acceso
    public function getConsumoAttribute($value)
    {
        return $value === 'S' ? 'Si' : 'No';
    }

    public function getEstadoAttribute($value)
    {
        return $value  === 'N' ? 'No tributable' : 'Tributable';
    }
}
