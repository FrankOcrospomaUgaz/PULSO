<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Campo;


class Tabla extends Model
{
    

    protected $perPage = 10;
    protected $table = 'tablas';
    protected $primaryKey = 'id';
    protected $fillable = ['descripcion', 'comentario', 'tipo', 'estado'];

    public function compo()
    {
        return $this->hasMany(Campo::class);
    }
    public function getEstadoAttribute($value)
    {
        return $value ? 'Normal' : 'Eliminado';
    }


}
