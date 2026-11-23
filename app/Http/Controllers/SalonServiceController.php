<?php

namespace App\Http\Controllers;

use App\Models\SalonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SalonServiceController extends Controller
{
    public function index()
    {
        $salonServices = SalonService::paginate();
        return view('salon-service.index', compact('salonServices'))
            ->with('i', (request()->input('page', 1) - 1) * $salonServices->perPage());
    }

    public function create($salonid)
    {
        $salonService = new SalonService();
        return view('salon-service.create', compact('salonService', 'salonid'));
    }
public function store(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'salon_id'     => 'required|integer|exists:salon,id',
            'titulo'       => 'required|string|max:255',
            'descripcion'  => 'nullable|string',
            'icono'        => 'nullable|string|max:255', // ← ahora es texto
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $service = SalonService::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Servicio de salón creado correctamente.',
            'redirect_url' => route('salon.servicios', $request->salon_id),
            'service' => $service
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'errors' => $e->getMessage(),
        ], 500);
    }
}


    public function show($id)
    {
        $salonService = SalonService::findOrFail($id);
        return view('salon-service.show', compact('salonService'));
    }

    public function edit($salonid, $serviceId)
    {
        $salonService = SalonService::findOrFail($serviceId);
        return view('salon-service.edit', compact('salonService', 'salonid'));
    }
public function update(Request $request, $salonid, $serviceId)
{
    $salonService = SalonService::findOrFail($serviceId);

    DB::beginTransaction();

    try {
        $validator = Validator::make($request->all(), [
            'salon_id'     => 'required|integer|exists:salon,id',
            'titulo'       => 'required|string|max:255',
            'descripcion'  => 'nullable|string',
            'icono'        => 'nullable|string|max:255', // ← ahora texto
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $salonService->update($data);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Servicio de salón actualizado correctamente.',
            'redirect_url' => route('salon.servicios', $request->salon_id),
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'errors' => $e->getMessage(),
        ], 500);
    }
}

    public function destroy($salonid, $serviceId)
    {
        try {
            $salonService = SalonService::findOrFail($serviceId);


            $salonService->delete();

            return response()->json([
                'success' => true,
                'message' => 'Servicio de salón eliminado correctamente.',
                'redirect_url' => route('salon.servicios', $salonid),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
}
