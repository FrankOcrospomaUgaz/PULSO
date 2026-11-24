<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PacienteClinico extends Model
{
    use SoftDeletes;

    protected $table = 'paciente_clinico';
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];
    protected $casts = ['fecha_nacimiento' => 'date', 'acepta_notificaciones' => 'boolean', 'acepta_notificaciones_at' => 'datetime'];

    public function citas()
    {
        return $this->hasMany(CitaClinica::class, 'paciente_id');
    }
}
