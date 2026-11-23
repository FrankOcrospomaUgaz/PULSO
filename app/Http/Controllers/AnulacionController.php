<?php

namespace App\Http\Controllers;

use App\Models\Anulacion;
use App\Http\Requests\AnulacionRequest;

/**
 * Class AnulacionController
 * @package App\Http\Controllers
 */
class AnulacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $anulacions = Anulacion::paginate();

        return view('anulacion.index', compact('anulacions'))
            ->with('i', (request()->input('page', 1) - 1) * $anulacions->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $anulacion = new Anulacion();
        return view('anulacion.create', compact('anulacion'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AnulacionRequest $request)
    {
        Anulacion::create($request->validated());

        return redirect()->route('anulacions.index')
            ->with('success', 'Anulacion created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $anulacion = Anulacion::find($id);

        return view('anulacion.show', compact('anulacion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $anulacion = Anulacion::find($id);

        return view('anulacion.edit', compact('anulacion'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AnulacionRequest $request, Anulacion $anulacion)
    {
        $anulacion->update($request->validated());

        return redirect()->route('anulacions.index')
            ->with('success', 'Anulacion updated successfully');
    }

    public function destroy($id)
    {
        Anulacion::find($id)->delete();

        return redirect()->route('anulacions.index')
            ->with('success', 'Anulacion deleted successfully');
    }
}
