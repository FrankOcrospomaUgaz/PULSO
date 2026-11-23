<?php

namespace App\Http\Controllers;

use App\Models\Perfile;
use App\Http\Requests\PerfileRequest;
use App\Models\Operacione;
use App\Models\Perfilsucursal;
use App\Models\RelSeccionsucursal;
use App\Models\RelSeccionsucursalperfil;
use App\Models\Seccione;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class PerfileController
 * @package App\Http\Controllers
 */
class PerfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10);

        $query = Perfile::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('descripcion', 'ILIKE', "%{$search}%")
                    ->orWhere('abreviatura', 'ILIKE', "%{$search}%")
                    ->orWhere('id', 'ILIKE', "%{$search}%");
            });
        }

        $perfiles = $query->paginate($perPage)->appends([
            'search' => $search,
            'perPage' => $perPage,
        ]);

        $operaciones = Operacione::where('tabla_id', 10) // asegúrate de usar el ID correcto
            ->where('estado', true)
            ->get();

        return view('perfile.index', compact('perfiles', 'operaciones', 'search', 'perPage'))
            ->with('i', (request()->input('page', 1) - 1) * $perfiles->perPage());
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $perfile = new Perfile();
        return view('perfile.create', compact('perfile'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PerfileRequest $request)
    {
        DB::beginTransaction(); // Iniciar la transacción

        try {
            // Crear el perfil
            $perfile = Perfile::create($request->validated());

            // Obtener las sucursales activas
            $sucursalesActivas = Sucursal::where('situacion', 'A')->get();

            // Crear una asociación en Perfilsucursal para cada sucursal activa
            foreach ($sucursalesActivas as $sucursal) {
                // Crear la relación en Perfilsucursal
                $perfilsucursal = Perfilsucursal::create([
                    'perfil_id' => $perfile->id, // ID del perfil recién creado
                    'sucursal_id' => $sucursal->id, // ID de la sucursal activa
                ]);

                // Obtener las relaciones activas entre las secciones y sucursales
                $relSeccionesSucursal = RelSeccionsucursal::where('situacion', 'A')
                    ->where('sucursal_id', $sucursal->id)
                    ->get();

                // Crear la relación en RelSeccionsucursalperfil para cada sección activa relacionada con la sucursal
                foreach ($relSeccionesSucursal as $relSeccion) {
                    // Lógica para asignar el valor correcto a menuopcionesdefecto_id
                    $menuopcionesdefecto_id = ($relSeccion->seccion_id == 3) ? 52 : 15;

                    RelSeccionsucursalperfil::create([
                        'perfil_id' => $perfile->id, // ID del perfil recién creado
                        'sucursal_id' => $sucursal->id, // ID de la sucursal activa
                        'seccion_id' => $relSeccion->seccion_id, // ID de la sección activa
                        'menuopcionesdefecto_id' => $menuopcionesdefecto_id, // Valor calculado para menuopcionesdefecto_id
                    ]);
                }
            }

            // Confirmar la transacción
            DB::commit();

            // Devolver la respuesta
            return response()->json([
                'success' => true,
                'message' => 'Perfil creado exitosamente con sus asociaciones a las sucursales activas y secciones.',
                'perfile' => $perfile,
                'redirect_url' => route('perfiles.index', ['page' => $request->input('pagina_actual', 1)]),
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir la transacción si ocurre un error

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $perfile = Perfile::find($id);
        return view('perfile.edit', compact('perfile'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PerfileRequest $request, Perfile $perfile)
    {
        try {
            $perfile->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Perfil actualizado exitosamente.',
                'perfile' => $perfile,
                'redirect_url' => route('perfiles.index')
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
            Perfile::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Perfil eliminado correctamente.',
                'redirect_url' => route('perfiles.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
