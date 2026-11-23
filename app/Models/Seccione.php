<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Seccione
 *
 * @property $id
 * @property $descripcion
 * @property $abreviatura
 * @property $orden
 * @property $icono
 * @property $color
 * @property $subtitulo
 * @property $comentario
 * @property $situacion
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Seccione extends Model
{
    use SoftDeletes;


    protected $perPage = 10;
    protected $table = 'secciones';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['descripcion', 'abreviatura', 'orden', 'icono', 'color', 'subtitulo', 'comentario', 'situacion'];

    protected $attributes = [
    
        'color' => '',
      
    ];
    public function getSituacionAttribute($value)
    {
        return $value === 'A' ? 'Activado' : 'Desactivado';
    }
    

}
