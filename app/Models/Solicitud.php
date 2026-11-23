<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Solicitud
 *
 * @property $id
 * @property $tipo_solicitud
 * @property $data
 * @property $fecha
 * @property $fechaenvio
 * @property $fecharespuesta
 * @property $fechaanulacion
 * @property $estado
 * @property $nombre
 * @property $total
 * @property $moneda
 * @property $tipo_documento
 * @property $serie
 * @property $correlativo
 * @property $ticket_solicitud
 * @property $persona_id
 * @property $json_persona
 * @property $sucursal_id
 * @property $json_sucursal
 * @property $facturador_id
 * @property $json_facturador
 * @property $movimiento_id
 * @property $solicitud_facturador_id
 * @property $error_code
 * @property $error_text
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 * @property $public_name
 *
 * @property Facturador $facturador
 * @property Movimiento $movimiento
 * @property Persona $persona
 * @property Sucursal $sucursal
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Solicitud extends Model
{
    use SoftDeletes;


    protected $table = 'solicitud';

    protected $perPage = 10;


    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['tipo_solicitud', 'data', 'fecha', 'fechaenvio', 'fecharespuesta', 'fechaanulacion', 'estado', 'nombre', 'total', 'moneda', 'tipo_documento', 'serie', 'correlativo', 'ticket_solicitud', 'persona_id', 'json_persona', 'sucursal_id', 'json_sucursal', 'facturador_id', 'json_facturador', 'movimiento_id', 'solicitud_facturador_id', 'error_code', 'error_text', 'public_name'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function facturador()
    {
        return $this->belongsTo(\App\Models\Facturador::class, 'facturador_id', 'id');
    }
    
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
    public function persona()
    {
        return $this->belongsTo(\App\Models\Persona::class, 'persona_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id');
    }
    

}
