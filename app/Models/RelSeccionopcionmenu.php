<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class RelSeccionopcionmenu
 *
 * @property $seccion_id
 * @property $opcionmenu_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Menuopcione $menuopcione
 * @property Seccione $seccione
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class RelSeccionopcionmenu extends Model
{
    use SoftDeletes;

    protected $perPage = 10;
    protected $table = 'rel_seccionopcionmenu';


    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['seccion_id', 'opcionmenu_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function menuopcione()
    {
        return $this->belongsTo(\App\Models\Menuopcione::class, 'opcionmenu_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function seccione()
    {
        return $this->belongsTo(\App\Models\Seccione::class, 'seccion_id', 'id');
    }
    

}
