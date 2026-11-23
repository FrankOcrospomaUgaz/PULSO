<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CuentaCobrarpagar extends Model
{
    use SoftDeletes;

    protected $table = 'cuenta_cobrarpagar';

    protected $fillable = [
        'numero',
        'movcaja_id',
        'tipo',
        'estado',
        'fecha',
        'fechavencimiento',
        'fechapago',
        'moneda',
        'tipocambio',
        'totalpagado',
        'situacion',
        'sucursal_id',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'fechavencimiento' => 'datetime',
        'fechapago' => 'datetime',
        'tipocambio' => 'decimal:3',
        'totalpagado' => 'decimal:2',
    ];

    public function movcaja()
    {
        return $this->belongsTo(Movcaja::class, 'movcaja_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetCuentaCobrarpagar::class, 'cuenta_cobrarpagar_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function getSituacionTextoAttribute(): string
    {
        return $this->situacion === 'A' || $this->situacion === 'E' ? 'Activado' : 'Desactivado';
    }

    public function getTotalAttribute(): float
    {
        return (float) ($this->movcaja->total ?? 0);
    }

    public function getRestanteAttribute(): float
    {
        return max(0, $this->total - (float) $this->totalpagado);
    }
}
