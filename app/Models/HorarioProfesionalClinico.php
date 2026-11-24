<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioProfesionalClinico extends Model
{
    protected $table = 'horario_profesional_clinico';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $casts = ['activo' => 'boolean'];

    public function profesional() { return $this->belongsTo(ProfesionalClinico::class, 'profesional_id'); }
}
