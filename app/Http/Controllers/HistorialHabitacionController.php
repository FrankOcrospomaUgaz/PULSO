<?php

namespace App\Http\Controllers;

use App\Models\HistorialHabitacion;
use App\Http\Requests\HistorialHabitacionRequest;

/**
 * Class HistorialHabitacionController
 * @package App\Http\Controllers
 */
class HistorialHabitacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $historialHabitacions = HistorialHabitacion::paginate();

        return view('historial-habitacion.index', compact('historialHabitacions'))
            ->with('i', (request()->input('page', 1) - 1) * $historialHabitacions->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $historialHabitacion = new HistorialHabitacion();
        return view('historial-habitacion.create', compact('historialHabitacion'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HistorialHabitacionRequest $request)
    {
        HistorialHabitacion::create($request->validated());

        return redirect()->route('historial-habitacions.index')
            ->with('success', 'HistorialHabitacion created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $historialHabitacion = HistorialHabitacion::find($id);

        return view('historial-habitacion.show', compact('historialHabitacion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $historialHabitacion = HistorialHabitacion::find($id);

        return view('historial-habitacion.edit', compact('historialHabitacion'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HistorialHabitacionRequest $request, HistorialHabitacion $historialHabitacion)
    {
        $historialHabitacion->update($request->validated());

        return redirect()->route('historial-habitacions.index')
            ->with('success', 'HistorialHabitacion updated successfully');
    }

    public function destroy($id)
    {
        HistorialHabitacion::find($id)->delete();

        return redirect()->route('historial-habitacions.index')
            ->with('success', 'HistorialHabitacion deleted successfully');
    }
}
