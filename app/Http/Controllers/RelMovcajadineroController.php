<?php

namespace App\Http\Controllers;

use App\Models\RelMovcajadinero;
use App\Http\Requests\RelMovcajadineroRequest;

/**
 * Class RelMovcajadineroController
 * @package App\Http\Controllers
 */
class RelMovcajadineroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $relMovcajadineros = RelMovcajadinero::paginate();

        return view('rel-movcajadinero.index', compact('relMovcajadineros'))
            ->with('i', (request()->input('page', 1) - 1) * $relMovcajadineros->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $relMovcajadinero = new RelMovcajadinero();
        return view('rel-movcajadinero.create', compact('relMovcajadinero'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RelMovcajadineroRequest $request)
    {
        RelMovcajadinero::create($request->validated());

        return redirect()->route('rel-movcajadineros.index')
            ->with('success', 'RelMovcajadinero created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $relMovcajadinero = RelMovcajadinero::find($id);

        return view('rel-movcajadinero.show', compact('relMovcajadinero'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $relMovcajadinero = RelMovcajadinero::find($id);

        return view('rel-movcajadinero.edit', compact('relMovcajadinero'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RelMovcajadineroRequest $request, RelMovcajadinero $relMovcajadinero)
    {
        $relMovcajadinero->update($request->validated());

        return redirect()->route('rel-movcajadineros.index')
            ->with('success', 'RelMovcajadinero updated successfully');
    }

    public function destroy($id)
    {
        RelMovcajadinero::find($id)->delete();

        return redirect()->route('rel-movcajadineros.index')
            ->with('success', 'RelMovcajadinero deleted successfully');
    }
}
