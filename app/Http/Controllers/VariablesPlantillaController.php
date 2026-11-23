<?php

namespace App\Http\Controllers;

use App\Models\VariablesPlantilla;
use App\Http\Requests\VariablesPlantillaRequest;

/**
 * Class VariablesPlantillaController
 * @package App\Http\Controllers
 */
class VariablesPlantillaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $variablesPlantillas = VariablesPlantilla::paginate();

        return view('variables-plantilla.index', compact('variablesPlantillas'))
            ->with('i', (request()->input('page', 1) - 1) * $variablesPlantillas->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $variablesPlantilla = new VariablesPlantilla();
        return view('variables-plantilla.create', compact('variablesPlantilla'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VariablesPlantillaRequest $request)
    {
        VariablesPlantilla::create($request->validated());

        return redirect()->route('variables-plantillas.index')
            ->with('success', 'VariablesPlantilla created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $variablesPlantilla = VariablesPlantilla::find($id);

        return view('variables-plantilla.show', compact('variablesPlantilla'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $variablesPlantilla = VariablesPlantilla::find($id);

        return view('variables-plantilla.edit', compact('variablesPlantilla'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VariablesPlantillaRequest $request, VariablesPlantilla $variablesPlantilla)
    {
        $variablesPlantilla->update($request->validated());

        return redirect()->route('variables-plantillas.index')
            ->with('success', 'VariablesPlantilla updated successfully');
    }

    public function destroy($id)
    {
        VariablesPlantilla::find($id)->delete();

        return redirect()->route('variables-plantillas.index')
            ->with('success', 'VariablesPlantilla deleted successfully');
    }
}
