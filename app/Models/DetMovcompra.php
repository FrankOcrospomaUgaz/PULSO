<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetMovcompra extends Model
{
    use SoftDeletes;

    protected $table = 'det_movcompra';

    protected $perPage = 10;

    protected $fillable = [
        'tipo_detalle',
        'movcompra_id',
        'codigo',
        'descripcion',
        'producto_id',
        'json_producto',
        'unidad_id',
        'igv_id',
        'cantidad',
        'monto',
        'comentario',
        'situacion',
        'sucursal_id',
    ];

    public function movcompra()
    {
        return $this->belongsTo(Movcompra::class, 'movcompra_id', 'id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'id');
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_id', 'id');
    }

    public function igv()
    {
        return $this->belongsTo(Igv::class, 'igv_id', 'id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id');
    }
}
