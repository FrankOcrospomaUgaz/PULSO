<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PlantillaContrato
 *
 * @property $id
 * @property $titulo
 * @property $contenido_html
 * @property $descripcion
 * @property $sucursal_id
 * @property $tipo_ambiente_id
 * @property $deleted_at
 * @property $created_at
 * @property $updated_at
 *
 * @property Sucursal $sucursal
 * @property Tipoambiente $tipoambiente
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class PlantillaContrato extends Model
{
    use SoftDeletes;



    protected $perPage = 10;

    protected $table = 'plantilla_contrato';
    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['titulo', 'contenido_html', 'descripcion', 'sucursal_id', 'tipo_ambiente_id'];


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
    public function tipoambiente()
    {
        return $this->belongsTo(\App\Models\Tipoambiente::class, 'tipo_ambiente_id', 'id');
    }
  
}
