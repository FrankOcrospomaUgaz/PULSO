<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use App\Models\Tipohabitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AmenityController extends Controller
{
    /**
     * Crear una nueva amenidad para un tipo de habitación
     */
    public function store(Request $request, $tipohabitacionId)
    {
        $validator = Validator::make($request->all(), [
            'amenities' => 'required|array|min:1',
            'amenities.*.nombre' => 'required|string|max:255',
            'amenities.*.icon' => 'nullable|string',
            'amenities.*.id' => 'nullable|integer|exists:amenities,id', // para actualización
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $tipohabitacion = Tipohabitacion::findOrFail($tipohabitacionId);
            $processedAmenities = [];

            foreach ($request->input('amenities') as $amenityData) {
                if (isset($amenityData['id'])) {
                    // Actualizar amenidad existente
                    $amenity = Amenity::where('id', $amenityData['id'])
                        ->where('tipohabitacion_id', $tipohabitacion->id)
                        ->first();

                    if ($amenity) {
                        $amenity->update([
                            'nombre' => $amenityData['nombre'],
                            'icon' => $amenityData['icon'] ?? '',
                        ]);
                        $processedAmenities[] = $amenity;
                    }
                } else {
                    // Crear nueva amenidad
                    $processedAmenities[] = Amenity::create([
                        'tipohabitacion_id' => $tipohabitacion->id,
                        'nombre' => $amenityData['nombre'],
                        'icon' => $amenityData['icon'] ?? '',
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Amenidades procesadas correctamente.',
                'data' => $processedAmenities,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Obtener todas las amenities de un tipo de habitación
     */
    public function getByTipoHabitacion($tipohabitacionId)
    {
        try {


            $amenities = Amenity::where('tipohabitacion_id', $tipohabitacionId)->select('id', 'nombre', 'icon', 'tipohabitacion_id')->get();

            return response()->json([
                'success' => true,
                'data' => $amenities,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener todas las amenities en general
     */
    public function getAll($sucursal_id)
    {
        try {
            $amenities = Amenity::select('id', 'nombre', 'icon', 'tipohabitacion_id')
                ->whereHas('tipohabitacion', function ($query) use ($sucursal_id) {
                    $query->where('sucursal_id', $sucursal_id);
                })
                ->get();

            return response()->json([
                'success' => true,
                'data' => $amenities,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($amenityId)
{
    try {
        // Buscar la amenidad por ID
        $amenity = Amenity::findOrFail($amenityId);

        // Eliminar la amenidad
        $amenity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Amenidad eliminada correctamente.',
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'errors' => 'Error al eliminar la amenidad: ' . $e->getMessage(),
        ], 500);
    }
}

}
