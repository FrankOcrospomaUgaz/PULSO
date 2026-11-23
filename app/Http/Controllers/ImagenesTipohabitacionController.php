<?php

namespace App\Http\Controllers;

use App\Models\ImagenesTipohabitacion;
use App\Models\Tipohabitacion;
use App\Support\LandingAssetImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator as FacadesValidator;


/**
 * Class ImagenesTipohabitacionController
 * @package App\Http\Controllers
 */
class ImagenesTipohabitacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // Método para subir una imagen
    public function storeImage(Request $request, $tipohabitacionId)
    {
        // Validación para asegurarse de que se suba una imagen
        $validator = FacadesValidator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,svg|max:2048',
            'descripcion' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            // Obtener el tipo de habitación
            $tipohabitacion = Tipohabitacion::findOrFail($tipohabitacionId);

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $image = $request->file('image');
                $url = LandingAssetImage::store($image);

                // Guardar la ruta en la base de datos
                ImagenesTipohabitacion::create([
                    'tipohabitacion_id' => $tipohabitacion->id,
                    'url' => $url, // Ruta pública relativa
                    'descripcion' => $request->input('descripcion', ''),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Imagen subida correctamente.',
                    'url' => $url,
                ], 201);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se ha subido una imagen válida.',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    // Método para obtener las imágenes de un tipo de habitación
    public function getImages($tipohabitacionId)
    {
        try {
            // Obtener las imágenes asociadas al tipo de habitación
            $tipohabitacion = Tipohabitacion::findOrFail($tipohabitacionId);

            $imagenes = $tipohabitacion->imagenes->map(function ($imagen) {
                $imagen->url = LandingAssetImage::fromExistingPath($imagen->url);
                return $imagen;
            });

            return response()->json([
                'success' => true,
                'data' => $imagenes, // Devolver las imágenes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
    public function getAllTiposHabitacion($sucursal_id)
    {
        try {
            // Obtener todos los tipos de habitación con las imágenes y amenities asociadas
            $tiposHabitacion = Tipohabitacion::with(['imagenes', 'amenities'])
                ->where('sucursal_id', $sucursal_id)
                ->get();

            // Preparar los datos a devolver para cada tipo de habitación
            $data = $tiposHabitacion->map(function ($tipoHabitacion) {
                // Filtrar solo las habitaciones disponibles
                $habitacionesDisponibles = $tipoHabitacion->habitaciones->filter(function ($habitacion) {
                    return $habitacion->situacion == 'Disponible';
                });

                // Transformar imágenes para ocultar timestamps
                $imagenes = $tipoHabitacion->imagenes->map(function ($imagen) {
                    return [
                        'id' => $imagen->id,
                        'url' => LandingAssetImage::fromExistingPath($imagen->url),
                        'descripcion' => $imagen->descripcion,
                        'tipohabitacion_id' => $imagen->tipohabitacion_id,
                    ];
                });

                // Transformar amenities para ocultar timestamps
                $amenities = $tipoHabitacion->amenities->map(function ($amenity) {
                    return [
                        'id' => $amenity->id,
                        'nombre' => $amenity->nombre,
                        'icon' => $amenity->icon,
                        'tipohabitacion_id' => $amenity->tipohabitacion_id,
                    ];
                });

                // Separar la descripción en un array usando '+' como delimitador
                $descripcionArray = explode('+', $tipoHabitacion->descripcion);

                return [
                    'id' => $tipoHabitacion->id,
                    'nombre' => $tipoHabitacion->nombre,
                    'precio' => $tipoHabitacion->precio,
                    'capacidad' => $tipoHabitacion->capacidad,
                    'descripcion' => $descripcionArray,
                    'imagenes' => $imagenes,
                    'amenities' => $amenities,
                    'habitaciones' => $habitacionesDisponibles,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }


    public function getTipoHabitacionById($id)
    {
        try {
            $tipoHabitacion = Tipohabitacion::with(['imagenes', 'amenities'])->findOrFail($id);

            // Separar la descripción en un array usando '+' como delimitador
            $descripcionArray = explode('+', $tipoHabitacion->descripcion);

            // Transformar amenities para ocultar timestamps
            $amenities = $tipoHabitacion->amenities->map(function ($amenity) {
                return [
                    'id' => $amenity->id,
                    'nombre' => $amenity->nombre,
                    'icon' => $amenity->icon,
                    'tipohabitacion_id' => $amenity->tipohabitacion_id,
                    // omitimos created_at, updated_at, deleted_at
                ];
            });

            // Preparar los datos a devolver
            $data = [
                'id' => $tipoHabitacion->id,
                'nombre' => $tipoHabitacion->nombre,
                'precio' => $tipoHabitacion->precio,
                'capacidad' => $tipoHabitacion->capacidad,
                'descripcion' => $descripcionArray,
                'imagenes' => $tipoHabitacion->imagenes->map(function ($imagen) {
                    $imagen->url = LandingAssetImage::fromExistingPath($imagen->url);
                    return $imagen;
                }),
                'amenities' => $amenities,
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }public function actualizarImagenes(Request $request, $id)
{
    try {
        $tipohabitacion = Tipohabitacion::findOrFail($id);

        // 1. Eliminar imágenes que fueron marcadas para eliminar
        if ($request->has('eliminar_imagenes')) {
            foreach ($request->input('eliminar_imagenes') as $imagenId) {
                $imagen = ImagenesTipohabitacion::find($imagenId);
                if ($imagen) {
                    // Eliminar la imagen del almacenamiento
                    Storage::delete('public/' . $imagen->url);
                    $imagen->delete();
                }
            }
        }

        // 2. Reemplazar imágenes existentes
        if ($request->has('reemplazar_imagenes')) {
            foreach ($request->input('reemplazar_imagenes') as $imagenId => $imagenData) {
                $imagen = ImagenesTipohabitacion::find($imagenId);
                if ($imagen) {
                    // Eliminar la imagen vieja del almacenamiento
                    Storage::delete('public/' . $imagen->url);

                    // Subir la nueva imagen
                    if ($request->hasFile("reemplazar_imagenes.{$imagenId}.imagen")) {
                        $image = $request->file("reemplazar_imagenes.{$imagenId}.imagen");
                        $url = LandingAssetImage::store($image);

                        // Actualizar la imagen y la descripción
                        $imagen->update([
                            'url' => $url,
                            'descripcion' => $imagenData['descripcion'],
                        ]);
                    } else {
                        // Si no se ha subido una nueva imagen, solo actualizar la descripción
                        $imagen->update([
                            'descripcion' => $imagenData['descripcion'],
                        ]);
                    }
                }
            }
        }

        // 3. Agregar nuevas imágenes
        if ($request->has('nuevas_imagenes')) {
            foreach ($request->input('nuevas_imagenes') as $index => $imagenData) {
                // Verifica si el archivo existe y es un archivo válido
                if ($request->hasFile("nuevas_imagenes.{$index}.imagen")) {
                    $image = $request->file("nuevas_imagenes.{$index}.imagen");
                    $url = LandingAssetImage::store($image);

                    // Crear la imagen en la base de datos
                    ImagenesTipohabitacion::create([
                        'tipohabitacion_id' => $tipohabitacion->id,
                        'url' => $url,
                        'descripcion' => $imagenData['descripcion'],
                    ]);
                }
            }
        }

        // Obtener la página actual para redireccionar
        $pagina_actual = $request->input('page', 1);

        return response()->json([
            'success' => true,
            'message' => 'Imágenes actualizadas correctamente.',
            'tipohabitacion' => $tipohabitacion,
            'redirect_url' => route('tipohabitacion.index', ['page' => $pagina_actual])
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'errors' => $e->getMessage()
        ], 500);
    }
}

}
