<?php

namespace App\Http\Controllers;

use App\Models\MovhabitacionMovimiento;
use App\Http\Requests\MovhabitacionMovimientoRequest;

/**
 * Class MovhabitacionMovimientoController
 * @package App\Http\Controllers
 */
class MovhabitacionMovimientoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $movhabitacionMovimientos = MovhabitacionMovimiento::paginate();

        return view('movhabitacion-movimiento.index', compact('movhabitacionMovimientos'))
            ->with('i', (request()->input('page', 1) - 1) * $movhabitacionMovimientos->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $movhabitacionMovimiento = new MovhabitacionMovimiento();
        return view('movhabitacion-movimiento.create', compact('movhabitacionMovimiento'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MovhabitacionMovimientoRequest $request)
    {
        MovhabitacionMovimiento::create($request->validated());

        return redirect()->route('movhabitacion-movimientos.index')
            ->with('success', 'MovhabitacionMovimiento created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $movhabitacionMovimiento = MovhabitacionMovimiento::find($id);

        return view('movhabitacion-movimiento.show', compact('movhabitacionMovimiento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $movhabitacionMovimiento = MovhabitacionMovimiento::find($id);

        return view('movhabitacion-movimiento.edit', compact('movhabitacionMovimiento'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MovhabitacionMovimientoRequest $request, MovhabitacionMovimiento $movhabitacionMovimiento)
    {
        $movhabitacionMovimiento->update($request->validated());

        return redirect()->route('movhabitacion-movimientos.index')
            ->with('success', 'MovhabitacionMovimiento updated successfully');
    }

    public function destroy($id)
    {
        MovhabitacionMovimiento::find($id)->delete();

        return redirect()->route('movhabitacion-movimientos.index')
            ->with('success', 'MovhabitacionMovimiento deleted successfully');
    }
}
