<?php

namespace App\Http\Controllers;

use App\Models\DetMovhabitacion;
use App\Http\Requests\DetMovhabitacionRequest;

/**
 * Class DetMovhabitacionController
 * @package App\Http\Controllers
 */
class DetMovhabitacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $detMovhabitacions = DetMovhabitacion::paginate();

        return view('det-movhabitacion.index', compact('detMovhabitacions'))
            ->with('i', (request()->input('page', 1) - 1) * $detMovhabitacions->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $detMovhabitacion = new DetMovhabitacion();
        return view('det-movhabitacion.create', compact('detMovhabitacion'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DetMovhabitacionRequest $request)
    {
        DetMovhabitacion::create($request->validated());

        return redirect()->route('det-movhabitacions.index')
            ->with('success', 'DetMovhabitacion created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $detMovhabitacion = DetMovhabitacion::find($id);

        return view('det-movhabitacion.show', compact('detMovhabitacion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $detMovhabitacion = DetMovhabitacion::find($id);

        return view('det-movhabitacion.edit', compact('detMovhabitacion'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DetMovhabitacionRequest $request, DetMovhabitacion $detMovhabitacion)
    {
        $detMovhabitacion->update($request->validated());

        return redirect()->route('det-movhabitacions.index')
            ->with('success', 'DetMovhabitacion updated successfully');
    }

    public function destroy($id)
    {
        DetMovhabitacion::find($id)->delete();

        return redirect()->route('det-movhabitacions.index')
            ->with('success', 'DetMovhabitacion deleted successfully');
    }
}
