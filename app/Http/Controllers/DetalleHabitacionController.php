<?php

namespace App\Http\Controllers;

use App\Models\DetalleHabitacion;
use App\Http\Requests\DetalleHabitacionRequest;

/**
 * Class DetalleHabitacionController
 * @package App\Http\Controllers
 */
class DetalleHabitacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $detalleHabitacions = DetalleHabitacion::paginate();

        return view('detalle-habitacion.index', compact('detalleHabitacions'))
            ->with('i', (request()->input('page', 1) - 1) * $detalleHabitacions->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $detalleHabitacion = new DetalleHabitacion();
        return view('detalle-habitacion.create', compact('detalleHabitacion'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DetalleHabitacionRequest $request)
    {
        DetalleHabitacion::create($request->validated());

        return redirect()->route('detalle-habitacions.index')
            ->with('success', 'DetalleHabitacion created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $detalleHabitacion = DetalleHabitacion::find($id);

        return view('detalle-habitacion.show', compact('detalleHabitacion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $detalleHabitacion = DetalleHabitacion::find($id);

        return view('detalle-habitacion.edit', compact('detalleHabitacion'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DetalleHabitacionRequest $request, DetalleHabitacion $detalleHabitacion)
    {
        $detalleHabitacion->update($request->validated());

        return redirect()->route('detalle-habitacions.index')
            ->with('success', 'DetalleHabitacion updated successfully');
    }

    public function destroy($id)
    {
        DetalleHabitacion::find($id)->delete();

        return redirect()->route('detalle-habitacions.index')
            ->with('success', 'DetalleHabitacion deleted successfully');
    }
}
