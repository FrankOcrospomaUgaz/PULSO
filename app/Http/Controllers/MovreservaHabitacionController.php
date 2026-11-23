<?php

namespace App\Http\Controllers;

use App\Models\MovreservaHabitacion;
use App\Http\Requests\MovreservaHabitacionRequest;

/**
 * Class MovreservaHabitacionController
 * @package App\Http\Controllers
 */
class MovreservaHabitacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $movreservaHabitacions = MovreservaHabitacion::paginate();

        return view('movreserva-habitacion.index', compact('movreservaHabitacions'))
            ->with('i', (request()->input('page', 1) - 1) * $movreservaHabitacions->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $movreservaHabitacion = new MovreservaHabitacion();
        return view('movreserva-habitacion.create', compact('movreservaHabitacion'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MovreservaHabitacionRequest $request)
    {
        MovreservaHabitacion::create($request->validated());

        return redirect()->route('movreserva-habitacions.index')
            ->with('success', 'MovreservaHabitacion created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $movreservaHabitacion = MovreservaHabitacion::find($id);

        return view('movreserva-habitacion.show', compact('movreservaHabitacion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $movreservaHabitacion = MovreservaHabitacion::find($id);

        return view('movreserva-habitacion.edit', compact('movreservaHabitacion'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MovreservaHabitacionRequest $request, MovreservaHabitacion $movreservaHabitacion)
    {
        $movreservaHabitacion->update($request->validated());

        return redirect()->route('movreserva-habitacions.index')
            ->with('success', 'MovreservaHabitacion updated successfully');
    }

    public function destroy($id)
    {
        MovreservaHabitacion::find($id)->delete();

        return redirect()->route('movreserva-habitacions.index')
            ->with('success', 'MovreservaHabitacion deleted successfully');
    }
}
