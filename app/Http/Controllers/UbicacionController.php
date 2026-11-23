<?php

namespace App\Http\Controllers;

use App\Models\Ubicacion;
use App\Http\Requests\UbicacionRequest;

/**
 * Class UbicacionController
 * @package App\Http\Controllers
 */
class UbicacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ubicacions = Ubicacion::paginate();

        return view('ubicacion.index', compact('ubicacions'))
            ->with('i', (request()->input('page', 1) - 1) * $ubicacions->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $ubicacion = new Ubicacion();
        return view('ubicacion.create', compact('ubicacion'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UbicacionRequest $request)
    {
        Ubicacion::create($request->validated());

        return redirect()->route('ubicacions.index')
            ->with('success', 'Ubicacion created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $ubicacion = Ubicacion::find($id);

        return view('ubicacion.show', compact('ubicacion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $ubicacion = Ubicacion::find($id);

        return view('ubicacion.edit', compact('ubicacion'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UbicacionRequest $request, Ubicacion $ubicacion)
    {
        $ubicacion->update($request->validated());

        return redirect()->route('ubicacions.index')
            ->with('success', 'Ubicacion updated successfully');
    }

    public function destroy($id)
    {
        Ubicacion::find($id)->delete();

        return redirect()->route('ubicacions.index')
            ->with('success', 'Ubicacion deleted successfully');
    }
}
