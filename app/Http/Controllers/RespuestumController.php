<?php

namespace App\Http\Controllers;

use App\Models\Respuestum;
use App\Http\Requests\RespuestumRequest;

/**
 * Class RespuestumController
 * @package App\Http\Controllers
 */
class RespuestumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $respuesta = Respuestum::paginate();

        return view('respuestum.index', compact('respuesta'))
            ->with('i', (request()->input('page', 1) - 1) * $respuesta->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $respuestum = new Respuestum();
        return view('respuestum.create', compact('respuestum'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RespuestumRequest $request)
    {
        Respuestum::create($request->validated());

        return redirect()->route('respuesta.index')
            ->with('success', 'Respuestum created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $respuestum = Respuestum::find($id);

        return view('respuestum.show', compact('respuestum'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $respuestum = Respuestum::find($id);

        return view('respuestum.edit', compact('respuestum'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RespuestumRequest $request, Respuestum $respuestum)
    {
        $respuestum->update($request->validated());

        return redirect()->route('respuesta.index')
            ->with('success', 'Respuesta updated successfully');
    }
    public function destroy($id)
    {
        try {
            // Buscar la respuesta antes de eliminarla
            $respuesta = Respuestum::findOrFail($id);
            $encuestaId = $respuesta->encuesta_id; // Guardar el ID de la encuesta antes de eliminar
    
            // Eliminar la respuesta
            $respuesta->delete();
    
            return response()->json([
                'success' => true,
                'message' => 'Respuestas eliminada correctamente.',
                'redirect_url' => route('encuesta.respuestas', $encuestaId) // Usar la variable almacenada
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
    
}
