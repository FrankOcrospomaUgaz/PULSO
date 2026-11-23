<?php

namespace App\Http\Controllers;

use App\Models\RelCategoriasucursal;
use App\Http\Requests\RelCategoriasucursalRequest;

/**
 * Class RelCategoriasucursalController
 * @package App\Http\Controllers
 */
class RelCategoriasucursalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $relCategoriasucursals = RelCategoriasucursal::paginate();

        return view('rel-categoriasucursal.index', compact('relCategoriasucursals'))
            ->with('i', (request()->input('page', 1) - 1) * $relCategoriasucursals->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $relCategoriasucursal = new RelCategoriasucursal();
        return view('rel-categoriasucursal.create', compact('relCategoriasucursal'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RelCategoriasucursalRequest $request)
    {
        RelCategoriasucursal::create($request->validated());

        return redirect()->route('rel-categoriasucursals.index')
            ->with('success', 'RelCategoriasucursal created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $relCategoriasucursal = RelCategoriasucursal::find($id);

        return view('rel-categoriasucursal.show', compact('relCategoriasucursal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $relCategoriasucursal = RelCategoriasucursal::find($id);

        return view('rel-categoriasucursal.edit', compact('relCategoriasucursal'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RelCategoriasucursalRequest $request, RelCategoriasucursal $relCategoriasucursal)
    {
        $relCategoriasucursal->update($request->validated());

        return redirect()->route('rel-categoriasucursals.index')
            ->with('success', 'RelCategoriasucursal updated successfully');
    }

    public function destroy($id)
    {
        RelCategoriasucursal::find($id)->delete();

        return redirect()->route('rel-categoriasucursals.index')
            ->with('success', 'RelCategoriasucursal deleted successfully');
    }
}
