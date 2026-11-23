<?php

namespace App\Http\Controllers;

use App\Models\Listaunidad;
use App\Http\Requests\ListaunidadRequest;

/**
 * Class ListaunidadController
 * @package App\Http\Controllers
 */
class ListaunidadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $listaunidads = Listaunidad::paginate();

        return view('listaunidad.index', compact('listaunidads'))
            ->with('i', (request()->input('page', 1) - 1) * $listaunidads->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $listaunidad = new Listaunidad();
        return view('listaunidad.create', compact('listaunidad'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ListaunidadRequest $request)
    {
        Listaunidad::create($request->validated());

        return redirect()->route('listaunidads.index')
            ->with('success', 'Listaunidad created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $listaunidad = Listaunidad::find($id);

        return view('listaunidad.show', compact('listaunidad'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $listaunidad = Listaunidad::find($id);

        return view('listaunidad.edit', compact('listaunidad'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ListaunidadRequest $request, Listaunidad $listaunidad)
    {
        $listaunidad->update($request->validated());

        return redirect()->route('listaunidads.index')
            ->with('success', 'Listaunidad updated successfully');
    }

    public function destroy($id)
    {
        Listaunidad::find($id)->delete();

        return redirect()->route('listaunidads.index')
            ->with('success', 'Listaunidad deleted successfully');
    }
}
