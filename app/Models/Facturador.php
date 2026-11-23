<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Facturador
 *
 * @property $id
 * @property $empresa_facturador_id
 * @property $porcentaje_igv
 * @property $username_wsdl
 * @property $password_wsdl
 * @property $modo_autenticacion
 * @property $username_sunat
 * @property $password_sunat
 * @property $situacion
 * @property $sucursal_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Sucursal $sucursal
 * @property Solicitud[] $solicituds
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Facturador extends Model
{
    use SoftDeletes;



    protected $table = 'facturador';

    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['empresa_facturador_id', 'porcentaje_igv', 'username_wsdl', 'password_wsdl', 'modo_autenticacion', 'username_sunat', 'password_sunat', 'situacion', 'sucursal_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id');
    }
    public function empresa()
    {
        return $this->belongsTo(\App\Models\Empresa::class, 'empresa_facturador_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function solicituds()
    {
        return $this->hasMany(\App\Models\Solicitud::class, 'id', 'facturador_id');
    }
    

}
