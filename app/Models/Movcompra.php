<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Movcompra extends Model
{
    use SoftDeletes;

    protected $table = 'movcompra';

    protected $perPage = 10;

    protected $fillable = [
        'serie',
        'anio',
        'tipo_detalle',
        'incluye_igv',
        'tipo_pago',
        'afecta_caja',
        'moneda',
        'tipocambio',
        'subtotal',
        'igv',
        'total',
        'afecta_kardex',
        'movimiento_id',
        'sucursal_id',
    ];

    public function movimiento()
    {
        return $this->belongsTo(Movimiento::class, 'movimiento_id', 'id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id');
    }

    public function detMovcompras()
    {
        return $this->hasMany(DetMovcompra::class, 'movcompra_id', 'id');
    }

    public function movcaja()
    {
        return $this->hasOneThrough(
            Movcaja::class,
            Movimiento::class,
            'movimiento_id',
            'movimiento_id',
            'movimiento_id',
            'id'
        );
    }

    public function getIncluyeIgvTextoAttribute(): string
    {
        return $this->getRawOriginal('incluye_igv') === 'S' ? 'Sí' : 'No';
    }

    public function getTipoDetalleTextoAttribute(): string
    {
        return $this->tipo_detalle === 'GLOSA' ? 'Compra por glosa' : 'Compra detallada';
    }

    public function getTipoPagoTextoAttribute(): string
    {
        return $this->tipo_pago === 'CREDITO' ? 'Al crédito' : 'Al contado';
    }
}
