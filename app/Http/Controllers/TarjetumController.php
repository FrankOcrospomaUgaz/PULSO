<?php

namespace App\Http\Controllers;

use App\Models\Tarjetum;
use App\Http\Requests\TarjetumRequest;
use App\Models\Operacione;

/**
 * Class TarjetumController
 * @package App\Http\Controllers
 */
class TarjetumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener las operaciones relacionadas con la tabla 'tarjetum'
        $operaciones = Operacione::where('tabla_id', 39)
            ->where('estado', true)
            ->get();

        // Capturamos el término de búsqueda
        $search = request()->input('search');
        $perPage = request()->input('perPage', 10);
        // Query base
        $query = Tarjetum::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->Where('descripcion', 'ILIKE', "%{$search}%")
                    ->orWhere('tipo', 'ILIKE', "%{$search}%")
                    ->orWhere('orden', 'ILIKE', "%{$search}%")
                    ->orWhere('situacion', 'ILIKE', "%{$search}%")
                    ->orWhere('icono', 'ILIKE', "%{$search}%");
            });
        }

        // Paginación de los registros de Tarjetum
        $tarjeta = $query->orderBy('id', 'desc')->paginate($perPage);

        return view('tarjetum.index', compact('tarjeta', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $tarjeta->perPage());
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tarjetum = new Tarjetum();
        return view('tarjetum.create', compact('tarjetum'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TarjetumRequest $request)
    {
        try {
            // Crear una nueva Tarjetum
            $tarjetum = Tarjetum::create($request->validated());

            // Devolver una respuesta con el mensaje de éxito
            return response()->json([
                'success' => true,
                'message' => 'Tarjeta creada exitosamente.',
                'tarjetum' => $tarjetum,
                'redirect_url' => route('tarjeta.index')
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
        $tarjetum = Tarjetum::find($id);
        return view('tarjetum.edit', compact('tarjetum'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TarjetumRequest $request, Tarjetum $tarjetum)
    {
        try {
            // Actualizar el registro de Tarjetum
            $tarjetum->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Tarjeta actualizada exitosamente.',
                'tarjetum' => $tarjetum,
                'redirect_url' => route('tarjeta.index')
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
            Tarjetum::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tarjeta eliminada correctamente.',
                'redirect_url' => route('tarjeta.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }


    public function activar($id)
    {
        try {
            $tar = Tarjetum::find($id);
            $tar->update(['situacion' => true]);

            // Llamar a la función index para reutilizar la lógica de cargar la vista
            return response()->json([
                'success' => true,
                'message' => 'Tarjeta activada correctamente.',
                'redirect_url' => route('tarjeta.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function desactivar($id)
    {
        try {
            $tar = Tarjetum::find($id);
            $tar->update(['situacion' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Tarjeta desactivada correctamente.',
                'redirect_url' => route('tarjeta.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
