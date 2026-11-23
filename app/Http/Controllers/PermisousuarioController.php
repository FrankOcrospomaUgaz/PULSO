<?php

namespace App\Http\Controllers;

use App\Models\Permisousuario;
use App\Http\Requests\PermisousuarioRequest;
use App\Models\Perfilsucursal;
use Illuminate\Support\Facades\DB;

/**
 * Class PermisousuarioController
 * @package App\Http\Controllers
 */
class PermisousuarioController extends Controller
{
    public function getOpcionesMenu($perfilId, $sucursalId)
    {
        $opcionesMenu = DB::table('permisousuario as pu')
            ->join('menuopciones as mo', 'pu.menuopcion_id', '=', 'mo.id')
            ->join('tipoopciones as to', 'mo.tipoopcion_id', '=', 'to.id')
            ->select('mo.id', 'mo.descripcion', 'mo.orden', 'mo.icono', 'mo.accion', 'mo.tabla_id', 'mo.modulo_id', 'mo.tipoopcion_id', 'to.descripcion as tipoopcion_descripcion')
            ->where('pu.perfil_id', $perfilId)
            ->when($sucursalId != 39, function ($query) {
                $query->where('mo.modulo_id', '!=', 1);
            })
            ->distinct()
            ->get();
    
        return response()->json($opcionesMenu);
    }
    
    
    
}
