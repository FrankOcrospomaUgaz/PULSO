<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SalonPackage
 *
 * @property $id
 * @property $salon_id
 * @property $nombre
 * @property $precio
 * @property $content
 * @property $imagen
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Salon $salon
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class SalonPackage extends Model
{
    use SoftDeletes;

    protected $table = 'salon_packages';

    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['salon_id', 'nombre', 'precio', 'content', 'imagen'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function salon()
    {
        return $this->belongsTo(\App\Models\Salon::class, 'salon_id', 'id');
    }
    

}
