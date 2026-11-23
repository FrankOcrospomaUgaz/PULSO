<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SalonService
 *
 * @property $id
 * @property $salon_id
 * @property $titulo
 * @property $descripcion
 * @property $icono
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Salon $salon
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class SalonService extends Model
{
    use SoftDeletes;

    protected $table = 'salon_services';
    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['salon_id', 'titulo', 'descripcion', 'icono'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function salon()
    {
        return $this->belongsTo(\App\Models\Salon::class);
    }

}
