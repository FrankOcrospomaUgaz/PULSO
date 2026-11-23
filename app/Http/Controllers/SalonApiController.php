<?php

namespace App\Http\Controllers;
use App\Models\Salon;
use App\Models\SalonService;
use App\Models\SalonPackage;
use App\Support\LandingAssetImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


/**
 * Class SalonController
 * @package App\Http\Controllers
 */
class SalonApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */public function store(Request $request, $sucursal_id)
{
    $validator = Validator::make($request->all(), [
        'id' => 'nullable|exists:salon,id',
        'titulo' => 'required|string|max:255',
        'descripcion_corta' => 'required|string',
        'descripcion_larga' => 'required|string',
        'imagen' => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:4096',
        'servicios' => 'nullable|array',
        'servicios.*.titulo' => 'required|string',
        'servicios.*.descripcion' => 'required|string',
        'servicios.*.icono' => 'nullable|string',
        'paquetes' => 'nullable|array',
        'paquetes.*.nombre' => 'required|string',
        'paquetes.*.precio' => 'required|numeric',
        'paquetes.*.content' => 'nullable|string',
        'paquetes.*.imagen' => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:4096',
    ]);

    if ($validator->fails()) {
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }

    DB::beginTransaction();

    try {
        // Manejo imagen salón
        $imagenPath = null;
        if ($request->hasFile('imagen') && $request->file('imagen')->isValid()) {
            $image = $request->file('imagen');
            $imagenPath = LandingAssetImage::store($image);
        }

        if ($request->has('id')) {
            $salon = Salon::findOrFail($request->id);

            if ($imagenPath) {
                $salon->imagen = $imagenPath;
            }

            $salon->update([
                'titulo' => $request->titulo,
                'descripcion_corta' => $request->descripcion_corta,
                'descripcion_larga' => $request->descripcion_larga,
                'sucursal_id' => $sucursal_id,
            ]);

            $salon->servicios()->delete();
            $salon->paquetes()->delete();
        } else {
            $salon = Salon::create([
                'titulo' => $request->titulo,
                'descripcion_corta' => $request->descripcion_corta,
                'descripcion_larga' => $request->descripcion_larga,
                'imagen' => $imagenPath,
                'sucursal_id' => $sucursal_id,
            ]);
        }

        // Servicios
        if ($request->has('servicios')) {
            foreach ($request->servicios as $servicio) {
                $salon->servicios()->create([
                    'titulo' => $servicio['titulo'],
                    'descripcion' => $servicio['descripcion'],
                    'icono' => $servicio['icono'] ?? null,
                ]);
            }
        }

        // Paquetes e imágenes
        if ($request->has('paquetes')) {
            foreach ($request->paquetes as $index => $paquete) {
                $paqueteImagenPath = null;

                if ($request->hasFile("paquetes.$index.imagen") && $request->file("paquetes.$index.imagen")->isValid()) {
                    $paqueteImage = $request->file("paquetes.$index.imagen");
                    $paqueteImagenPath = LandingAssetImage::store($paqueteImage);
                }

                $salon->paquetes()->create([
                    'nombre' => $paquete['nombre'],
                    'precio' => $paquete['precio'],
                    'content' => $paquete['content'] ?? null,
                    'imagen' => $paqueteImagenPath,
                ]);
            }
        }

        DB::commit();

        $message = $request->has('id') ? 'Salón actualizado correctamente.' : 'Salón creado correctamente.';

        return response()->json(['success' => true, 'message' => $message], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

    
    // Listar todos los salones
    public function index($sucursal_id)
    {
        try {
            $salones = Salon::with(['servicios', 'paquetes'])
                ->where('sucursal_id', $sucursal_id)
                ->get()
                ->map(fn($salon) => $this->normalizeSalonImages($salon));

            return response()->json(['success' => true, 'data' => $salones]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // Obtener servicios de un salón
    public function getDetalles($salon_id)
    {
        try {
            $salon = Salon::with(['servicios', 'paquetes'])->findOrFail($salon_id);
            $salon = $this->normalizeSalonImages($salon);
    
            return response()->json([
                'success' => true,
                'data' => [
                    'salon' => $salon,
                    'servicios' => $salon->servicios,
                    'paquetes' => $salon->paquetes,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    private function normalizeSalonImages(Salon $salon): Salon
    {
        $salon->imagen = LandingAssetImage::fromExistingPath($salon->imagen);
        $salon->paquetes->each(function ($paquete) {
            $paquete->imagen = LandingAssetImage::fromExistingPath($paquete->imagen);
        });

        return $salon;
    }
    
}
