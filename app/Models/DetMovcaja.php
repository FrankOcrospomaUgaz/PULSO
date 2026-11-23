<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\CuentaCobrarpagarService;

/**
 * Class DetMovcaja
 *
 * @property $id
 * @property $movcaja_id
 * @property $tipo
 * @property $fechavencimiento
 * @property $fechapago
 * @property $mediopago_id
 * @property $mediopago
 * @property $numero
 * @property $tarjeta_id
 * @property $tarjeta
 * @property $banco_id
 * @property $banco
 * @property $billeteradigital_id
 * @property $billeteradigital
 * @property $pos_id
 * @property $pos
 * @property $monto
 * @property $comentario
 * @property $situacion
 * @property $sucursal_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Banco $banco
 * @property Billeteradigital $billeteradigital
 * @property Mediopago $mediopago
 * @property Movcaja $movcaja
 * @property Pasarelapago $pasarelapago
 * @property Sucursal $sucursal
 * @property Tarjetum $tarjetum
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class DetMovcaja extends Model
{
    use SoftDeletes;


    protected $table = 'det_movcaja';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['movcaja_id', 'tipo', 'fechavencimiento', 'fechapago', 'mediopago_id', 'mediopago', 'numero', 'tarjeta_id', 'tarjeta', 'banco_id', 'banco', 'billeteradigital_id', 'billeteradigital', 'pos_id', 'pos', 'monto', 'comentario', 'situacion', 'sucursal_id'];

    protected static function booted()
    {
        static::saved(function (DetMovcaja $detalle) {
            CuentaCobrarpagarService::sincronizarDesdeMovcajaId($detalle->movcaja_id);
        });

        static::deleted(function (DetMovcaja $detalle) {
            CuentaCobrarpagarService::sincronizarDesdeMovcajaId($detalle->movcaja_id);
        });
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function banco()
    {
        return $this->belongsTo(\App\Models\Banco::class, 'banco_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function billeteradigital()
    {
        return $this->belongsTo(\App\Models\Billeteradigital::class, 'billeteradigital_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function Mediopago()
    {
        return $this->belongsTo(\App\Models\Mediopago::class, 'mediopago_id', 'id');
        
    }
        public function Mediopagos()
    {
        return $this->belongsTo(\App\Models\Mediopago::class, 'mediopago_id', 'id');
        
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function movcaja()
    {
        return $this->belongsTo(\App\Models\Movcaja::class, 'movcaja_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pasarelapago()
    {
        return $this->belongsTo(\App\Models\Pasarelapago::class, 'pos_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tarjetum()
    {
        return $this->belongsTo(\App\Models\Tarjetum::class, 'tarjeta_id', 'id');
    }
    

}
