<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Piso
 *
 * @property $id
 * @property $nombre
 * @property $estado
 * @property $created_at
 * @property $updated_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Piso extends Model

{
    use SoftDeletes;


    protected $perPage = 10;

    protected $table = 'piso';

    protected $dates = ['deleted_at'];
    protected $fillable = ['nombre', 'estado', 'sucursal_id'];

    public function getEstadoAttribute($value)
    {
        return $value ? 'Activo' : 'Eliminado';
    }


}
