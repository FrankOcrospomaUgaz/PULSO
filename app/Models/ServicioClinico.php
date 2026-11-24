<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicioClinico extends Model
{
    use SoftDeletes;

    protected $table = 'servicio_clinico';
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];
    protected $casts = ['activo' => 'boolean', 'precio' => 'decimal:2'];

    public function citas()
    {
        return $this->hasMany(CitaClinica::class, 'servicio_id');
    }
}
