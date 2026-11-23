<?php

namespace App\Http\Controllers;

use App\Models\MovhabitacionHabitacion;
use App\Http\Requests\MovhabitacionHabitacionRequest;

/**
 * Class MovhabitacionHabitacionController
 * @package App\Http\Controllers
 */
class MovhabitacionHabitacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $movhabitacionHabitacions = MovhabitacionHabitacion::paginate();

        return view('movhabitacion-habitacion.index', compact('movhabitacionHabitacions'))
            ->with('i', (request()->input('page', 1) - 1) * $movhabitacionHabitacions->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $movhabitacionHabitacion = new MovhabitacionHabitacion();
        return view('movhabitacion-habitacion.create', compact('movhabitacionHabitacion'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MovhabitacionHabitacionRequest $request)
    {
        MovhabitacionHabitacion::create($request->validated());

        return redirect()->route('movhabitacion-habitacions.index')
            ->with('success', 'MovhabitacionHabitacion created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $movhabitacionHabitacion = MovhabitacionHabitacion::find($id);

        return view('movhabitacion-habitacion.show', compact('movhabitacionHabitacion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $movhabitacionHabitacion = MovhabitacionHabitacion::find($id);

        return view('movhabitacion-habitacion.edit', compact('movhabitacionHabitacion'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MovhabitacionHabitacionRequest $request, MovhabitacionHabitacion $movhabitacionHabitacion)
    {
        $movhabitacionHabitacion->update($request->validated());

        return redirect()->route('movhabitacion-habitacions.index')
            ->with('success', 'MovhabitacionHabitacion updated successfully');
    }

    public function destroy($id)
    {
        MovhabitacionHabitacion::find($id)->delete();

        return redirect()->route('movhabitacion-habitacions.index')
            ->with('success', 'MovhabitacionHabitacion deleted successfully');
    }
}
