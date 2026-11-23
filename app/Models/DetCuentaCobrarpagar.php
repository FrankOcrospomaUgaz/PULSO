<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetCuentaCobrarpagar extends Model
{
    use SoftDeletes;

    protected $table = 'det_cuenta_cobrarpagar';

    protected $fillable = [
        'cuenta_cobrarpagar_id',
        'movcaja_id',
        'situacion',
        'sucursal_id',
    ];

    public function cuenta()
    {
        return $this->belongsTo(CuentaCobrarpagar::class, 'cuenta_cobrarpagar_id');
    }

    public function movcaja()
    {
        return $this->belongsTo(Movcaja::class, 'movcaja_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
}
