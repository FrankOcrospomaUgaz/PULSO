<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Sucursal
 *
 * @property $id
 * @property $razonsocial
 * @property $nombrecomercial
 * @property $direccion
 * @property $ruc
 * @property $email
 * @property $telefonofijo
 * @property $telefonomovil
 * @property $logo
 * @property $situacion
 * @property $facturacion
 * @property $tipo
 * @property $venta_mayorista
 * @property $cantidad_precioventa
 * @property $empresa_id
 * @property $ubigeo_id
 * @property $business_intelligence_url
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Empresa $empresa
 * @property Ubigeo $ubigeo
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Sucursal extends Model
{
    use SoftDeletes;
    protected $table = 'sucursal';


    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['razonsocial', 'nombrecomercial', 'direccion', 'ruc', 'email', 'telefonofijo', 'telefonomovil', 'logo', 'situacion', 'facturacion', 'tipo', 'venta_mayorista', 'cantidad_precioventa', 'empresa_id', 'ubigeo_id', 'business_intelligence_url'];

    protected $attributes = [

        'logo' => '',
        'telefonofijo' => '',
        'telefonomovil' => '',
        'direccion' => '',
        'venta_mayorista' => '',
        'situacion' => 'A',
        'email' => '',
        'facturacion' => 'N',
        'venta_mayorista' => 'i',
        'cantidad_precioventa' => 0,
        'business_intelligence_url' => '',
];  
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function empresa()
    {
        return $this->belongsTo(\App\Models\Empresa::class, 'empresa_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ubigeo()
    {
        return $this->belongsTo(\App\Models\Ubigeo::class, 'ubigeo_id', 'id');
    }
    public function getSituacionAttribute($value)
    {
        return $value === 'A' ? 'Activado' : 'Desactivado';
    }
    
    public function getFacturacionAttribute($value)
    {
        return $value === 'S' ? 'Si' : 'No';
    }
    

}
