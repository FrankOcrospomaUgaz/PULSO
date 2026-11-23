<?php

namespace App\Http\Controllers;

use App\Models\Distribuidor;
use App\Http\Requests\DistribuidorRequest;

/**
 * Class DistribuidorController
 * @package App\Http\Controllers
 */
class DistribuidorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $distribuidors = Distribuidor::paginate();

        return view('distribuidor.index', compact('distribuidors'))
            ->with('i', (request()->input('page', 1) - 1) * $distribuidors->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $distribuidor = new Distribuidor();
        return view('distribuidor.create', compact('distribuidor'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DistribuidorRequest $request)
    {
        Distribuidor::create($request->validated());

        return redirect()->route('distribuidors.index')
            ->with('success', 'Distribuidor created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $distribuidor = Distribuidor::find($id);

        return view('distribuidor.show', compact('distribuidor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $distribuidor = Distribuidor::find($id);

        return view('distribuidor.edit', compact('distribuidor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DistribuidorRequest $request, Distribuidor $distribuidor)
    {
        $distribuidor->update($request->validated());

        return redirect()->route('distribuidors.index')
            ->with('success', 'Distribuidor updated successfully');
    }

    public function destroy($id)
    {
        Distribuidor::find($id)->delete();

        return redirect()->route('distribuidors.index')
            ->with('success', 'Distribuidor deleted successfully');
    }
}
