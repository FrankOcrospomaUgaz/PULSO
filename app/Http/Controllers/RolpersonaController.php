<?php

namespace App\Http\Controllers;

use App\Models\Rolpersona;
use App\Http\Requests\RolpersonaRequest;

/**
 * Class RolpersonaController
 * @package App\Http\Controllers
 */
class RolpersonaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rolpersonas = Rolpersona::paginate();

        return view('rolpersona.index', compact('rolpersonas'))
            ->with('i', (request()->input('page', 1) - 1) * $rolpersonas->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $rolpersona = new Rolpersona();
        return view('rolpersona.create', compact('rolpersona'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RolpersonaRequest $request)
    {
        Rolpersona::create($request->validated());

        return redirect()->route('rolpersonas.index')
            ->with('success', 'Rolpersona created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $rolpersona = Rolpersona::find($id);

        return view('rolpersona.show', compact('rolpersona'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $rolpersona = Rolpersona::find($id);

        return view('rolpersona.edit', compact('rolpersona'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RolpersonaRequest $request, Rolpersona $rolpersona)
    {
        $rolpersona->update($request->validated());

        return redirect()->route('rolpersonas.index')
            ->with('success', 'Rolpersona updated successfully');
    }

    public function destroy($id)
    {
        Rolpersona::find($id)->delete();

        return redirect()->route('rolpersonas.index')
            ->with('success', 'Rolpersona deleted successfully');
    }
}
