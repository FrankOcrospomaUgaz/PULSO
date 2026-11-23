<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class MovreservaPaquete
 *
 * @property $id
 * @property $observacion
 * @property $nropersonas
 * @property $fechavencimiento
 * @property $fechainicio
 * @property $fechasalida
 * @property $fechacancelacion
 * @property $precio_paquete
 * @property $total
 * @property $salon_package_id
 * @property $salon_package_name
 * @property $json_paquetes
 * @property $estado
 * @property $movimiento_id
 * @property $sucursal_id
 * @property $celular
 * @property $email
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Movimiento $movimiento
 * @property SalonPackage $salonPackage
 * @property Sucursal $sucursal
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class MovreservaPaquete extends Model
{
    use SoftDeletes;

    protected $table = 'movreserva_paquete';

    protected $perPage = 10;


    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['observacion', 'nropersonas', 'fechavencimiento', 'fechainicio', 'fechasalida', 'fechacancelacion', 'precio_paquete', 'total', 'salon_package_id', 'salon_package_name', 'json_paquetes', 'estado', 'movimiento_id', 'sucursal_id', 'celular', 'email'];


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
    public function salonPackage()
    {
        return $this->belongsTo(\App\Models\SalonPackage::class, 'salon_package_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id');
    }
    

}
