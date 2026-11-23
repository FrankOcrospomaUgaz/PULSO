<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Menuopcione
 *
 * @property $id
 * @property $descripcion
 * @property $orden
 * @property $icono
 * @property $accion
 * @property $tabla_id
 * @property $modulo_id
 * @property $tipoopcion_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 * @property $acceso_rapido
 * @property $condiciones_ar
 *
 * @property Modulo $modulo
 * @property Tabla $tabla
 * @property Tipoopcione $tipoopcione
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Menuopcione extends Model
{
    use SoftDeletes;


    protected $perPage = 10;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['descripcion', 'orden', 'icono', 'accion', 'tabla_id', 'modulo_id', 'tipoopcion_id', 'acceso_rapido', 'condiciones_ar', 'estado'];
    protected $attributes = [
            'icono' => ' ',
			'condiciones_ar' => '{}',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function modulo()
    {
        return $this->belongsTo(\App\Models\Modulo::class, 'modulo_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tabla()
    {
        return $this->belongsTo(\App\Models\Tabla::class, 'tabla_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tipoopcione()
    {
        return $this->belongsTo(\App\Models\Tipoopcione::class, 'tipoopcion_id', 'id');
    }

    public function relSeccionOpcionMenu()
    {
        return $this->belongsTo(\App\Models\RelSeccionopcionmenu::class, 'id', 'opcionmenu_id');
    }
    
    public function getEstadoAttribute($value)
    {
        return $value ? 'Normal' : 'Eliminado';
    }
}
