<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Http\Requests\RolRequest;
use App\Models\Operacione;
use Illuminate\Http\Request;

/**
 * Class RolController
 * @package App\Http\Controllers
 */
class RolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    $search = $request->input('search');
    $perPage = $request->input('perPage', 10);

    $query = Rol::query();

    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('descripcion', 'ILIKE', "%{$search}%")
              ->orWhere('id', 'ILIKE', "%{$search}%");
        });
    }

    $roles = $query->paginate($perPage)->appends([
        'search' => $search,
        'perPage' => $perPage,
    ]);

    $operaciones = Operacione::where('tabla_id', 35)
        ->where('estado', true)
        ->get();

    return view('rol.index', compact('roles', 'operaciones', 'search', 'perPage'))
        ->with('i', (request()->input('page', 1) - 1) * $roles->perPage());
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $rol = new Rol();
        return view('rol.create', compact('rol'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RolRequest $request)
    {
        try {
            $rol = Rol::create($request->validated());

            $pagina_actual = $request->input('pagina_actual', 1);

            return response()->json([
                'success' => true,
                'message' => 'Rol creado exitosamente.',
                'rol' => $rol,
                'redirect_url' => route('rol.index', ['page' => $pagina_actual])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $rol = Rol::find($id);
        return view('rol.edit', compact('rol'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RolRequest $request, Rol $rol)
    {
        try {
            $rol->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Rol actualizado exitosamente.',
                'rol' => $rol,
                'redirect_url' => route('rol.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            Rol::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rol eliminado correctamente.',
                'redirect_url' => route('rol.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
