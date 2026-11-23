<?php

namespace App\Http\Controllers;

use App\Models\Personamaestro;
use App\Http\Requests\PersonamaestroRequest;

/**
 * Class PersonamaestroController
 * @package App\Http\Controllers
 */
class PersonamaestroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $personamaestros = Personamaestro::paginate();

        return view('personamaestro.index', compact('personamaestros'))
            ->with('i', (request()->input('page', 1) - 1) * $personamaestros->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $personamaestro = new Personamaestro();
        return view('personamaestro.create', compact('personamaestro'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PersonamaestroRequest $request)
    {
        Personamaestro::create($request->validated());

        return redirect()->route('personamaestros.index')
            ->with('success', 'Personamaestro created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $personamaestro = Personamaestro::find($id);

        return view('personamaestro.show', compact('personamaestro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $personamaestro = Personamaestro::find($id);

        return view('personamaestro.edit', compact('personamaestro'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PersonamaestroRequest $request, Personamaestro $personamaestro)
    {
        $personamaestro->update($request->validated());

        return redirect()->route('personamaestros.index')
            ->with('success', 'Personamaestro updated successfully');
    }

    public function destroy($id)
    {
        Personamaestro::find($id)->delete();

        return redirect()->route('personamaestros.index')
            ->with('success', 'Personamaestro deleted successfully');
    }
}
