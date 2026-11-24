<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificacionClinica extends Model
{
    protected $table = 'notificacion_clinica';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $casts = ['fecha_evento' => 'date', 'respuesta' => 'array', 'enviado_at' => 'datetime'];

    public function paciente() { return $this->belongsTo(PacienteClinico::class, 'paciente_id'); }
    public function cita() { return $this->belongsTo(CitaClinica::class, 'cita_id'); }
}
