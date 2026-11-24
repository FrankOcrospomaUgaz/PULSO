<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CitaClinica extends Model
{
    use SoftDeletes;

    protected $table = 'cita_clinica';
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];
    protected $casts = ['inicio' => 'datetime', 'fin' => 'datetime'];

    public function paciente() { return $this->belongsTo(PacienteClinico::class, 'paciente_id'); }
    public function profesional() { return $this->belongsTo(ProfesionalClinico::class, 'profesional_id'); }
    public function servicio() { return $this->belongsTo(ServicioClinico::class, 'servicio_id'); }
}
