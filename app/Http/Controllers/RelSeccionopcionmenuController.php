<?php

namespace App\Http\Controllers;

use App\Models\RelSeccionopcionmenu;
use App\Http\Requests\RelSeccionopcionmenuRequest;
use App\Models\Menuopcione;
use App\Models\Permisousuario;
use App\Models\Persona;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Auth;

/**
 * Class RelSeccionopcionmenuController
 * @package App\Http\Controllers
 */
class RelSeccionopcionmenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $relSeccionopcionmenus = RelSeccionopcionmenu::paginate();

        return view('rel-seccionopcionmenu.index', compact('relSeccionopcionmenus'))
            ->with('i', (request()->input('page', 1) - 1) * $relSeccionopcionmenus->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $relSeccionopcionmenu = new RelSeccionopcionmenu();
        return view('rel-seccionopcionmenu.create', compact('relSeccionopcionmenu'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RelSeccionopcionmenuRequest $request)
    {
        RelSeccionopcionmenu::create($request->validated());

        return redirect()->route('rel-seccionopcionmenus.index')
            ->with('success', 'RelSeccionopcionmenu created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $relSeccionopcionmenu = RelSeccionopcionmenu::find($id);

        return view('rel-seccionopcionmenu.show', compact('relSeccionopcionmenu'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $relSeccionopcionmenu = RelSeccionopcionmenu::find($id);

        return view('rel-seccionopcionmenu.edit', compact('relSeccionopcionmenu'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RelSeccionopcionmenuRequest $request, RelSeccionopcionmenu $relSeccionopcionmenu)
    {
        $relSeccionopcionmenu->update($request->validated());

        return redirect()->route('rel-seccionopcionmenus.index')
            ->with('success', 'RelSeccionopcionmenu updated successfully');
    }

    public function destroy($id)
    {
        RelSeccionopcionmenu::find($id)->delete();

        return redirect()->route('rel-seccionopcionmenus.index')
            ->with('success', 'RelSeccionopcionmenu deleted successfully');
    }

    public function getMenu($id)
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursalId = $persona->sucursal_id ?? null;
        $perfilId = $user->perfil_id;

        // Obtener permisos del usuario autenticado
        $permisosUsuario = Permisousuario::join('persona', 'persona.sucursal_id', '=', 'permisousuario.sucursal_id')
            ->where('situacion', true)
            ->where('perfil_id', $perfilId)
            ->where('permisousuario.sucursal_id', $sucursalId)
            ->where('persona.id', $persona->id)
            ->pluck('menuopcion_id')
            ->toArray();

        // Obtener opciones de menú permitidas
        $opciones = Menuopcione::with(['modulo', 'tipoopcione'])
            ->whereIn('id', $permisosUsuario)
            ->whereHas('relSeccionOpcionMenu', function ($query) use ($id) {
                $query->where('seccion_id', $id);
            })
            ->orderBy('orden')
            ->get()
            ->groupBy('modulo_id');

        // Filtrar opciones de acceso rápido dentro de $opciones
        $menuopcionesaccesrapido = $opciones->flatMap(function ($items) {
            return $items->filter(fn($item) => $item->acceso_rapido === 'S');
        })->sortBy(fn($item) => $item->modulo->orden)->values();


        return response()->json([
            'success' => true,
            'opciones' => $opciones->sortBy(fn($menuItemsByModulo) => $menuItemsByModulo->first()->modulo->orden)
                ->map(function ($menuItemsByModulo) use ($sucursalId) {

                    $modulo = $menuItemsByModulo->first()->modulo;
                    return [
                        'modulo_id' => $modulo->id,
                        'modulo_descripcion' => $modulo->descripcion,
                        'modulo_icono' => $modulo->icono,
                        'tipos' => $menuItemsByModulo->groupBy('tipoopcion_id')->map(function ($menuItemsByTipoOpcion) use ($sucursalId) {
                            $tipoopcion = $menuItemsByTipoOpcion->first()->tipoopcione;
                            return [
                                'tipoopcion_id' => $tipoopcion->id,
                                'tipoopcion_descripcion' => $tipoopcion->descripcion,
                                'opciones' => $menuItemsByTipoOpcion->map(function ($opcion) use ($sucursalId) {
                                    try {
                                        $url = $sucursalId
                                            ? route($opcion->accion . '.index', ['sucursal_id' => $sucursalId])
                                            : route($opcion->accion . '.index');
                                    } catch (\Exception $e) {
                                        $url = '#';
                                    }

                                    return [
                                        'id' => $opcion->id,
                                        'descripcion' => $opcion->descripcion,
                                        'icono' => $opcion->icono,
                                        'url' => $url,
                                    ];
                                })->toArray()
                            ];
                        })->values()->toArray()
                    ];
                })->values()->toArray(),
            'accesoRapido' => $menuopcionesaccesrapido->map(function ($opcion) {
                return [
                    'id' => $opcion->id,
                    'descripcion' => $opcion->descripcion,
                    'icono' => $opcion->icono,
                    'nombre' => $opcion->nombre,
                    'url' => route($opcion->accion . '.index'),
                ];
            })->values()->toArray()

        ]);
    }
}
