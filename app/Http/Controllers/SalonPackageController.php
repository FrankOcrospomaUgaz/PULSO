<?php

namespace App\Http\Controllers;

use App\Models\SalonPackage;
use App\Support\LandingAssetImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SalonPackageController extends Controller
{
    public function index()
    {
        $salonPackages = SalonPackage::paginate();

        return view('salon-package.index', compact('salonPackages'))
            ->with('i', (request()->input('page', 1) - 1) * $salonPackages->perPage());
    }

    public function create($salonid)
    {
        $salonPackage = new SalonPackage();
        return view('salon-package.create', compact('salonPackage', 'salonid'));
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'salon_id' => 'required|integer|exists:salon,id',
                'nombre' => 'required|string|max:255',
                'precio' => 'required|numeric|min:0',
                'content' => 'nullable|string',
                'imagen' => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            // Procesar imagen si se sube
            if ($request->hasFile('imagen')) {
                $image = $request->file('imagen');
                $data['imagen'] = LandingAssetImage::store($image);
            }

            $paquete = SalonPackage::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Paquete de salón creado correctamente.',
                'paquete' => $paquete,
                'redirect_url' => route('salon.paquetes', $request->salon_id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $salonPackage = SalonPackage::findOrFail($id);
        return view('salon-package.show', compact('salonPackage'));
    }

    public function edit($salonid, $paqueteId)
    {
        $salonPackage = SalonPackage::findOrFail($paqueteId);
        return view('salon-package.edit', compact('salonPackage', 'salonid'));
    }

public function update(Request $request, $salonid, $paqueteId)
{
       
    $salonPackage = SalonPackage::findOrFail($paqueteId);

    DB::beginTransaction();

    try {
        $validator = Validator::make($request->all(), [
            'salon_id' => 'required|integer|exists:salon,id',
            'nombre'   => 'required|string|max:255',
            'precio'   => 'required|numeric|min:0',
            'content'  => 'nullable|string',
            'imagen'   => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Procesar imagen si se sube
        if ($request->hasFile('imagen') && $request->file('imagen')->isValid()) {
            // Eliminar la imagen anterior si existe
            if (!empty($request->imagen_antigua) && file_exists(public_path($request->imagen_antigua))) {
                unlink(public_path($request->imagen_antigua));
            }

            $image = $request->file('imagen');
            $data['imagen'] = LandingAssetImage::store($image);
        }
        
        // ✅ Actualiza sin crear uno nuevo
        $salonPackage->update($data);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Paquete de salón actualizado correctamente.',
            'redirect_url' => route('salon.paquetes', $request->salon_id)
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'errors' => $e->getMessage()
        ], 500);
    }
}

    public function destroy($salonid, $paqueteId)
    {
        try {
            $salonPackage = SalonPackage::findOrFail($paqueteId);

      

            $salonPackage->delete();

            return response()->json([
                'success' => true,
                'message' => 'Paquete de salón eliminado correctamente.',
                'redirect_url' => route('salon.paquetes', $salonid)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
