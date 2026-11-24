<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfesionalClinico extends Model
{
    use SoftDeletes;

    protected $table = 'profesional_clinico';
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];
    protected $casts = ['activo' => 'boolean'];

    public function citas()
    {
        return $this->hasMany(CitaClinica::class, 'profesional_id');
    }
}
