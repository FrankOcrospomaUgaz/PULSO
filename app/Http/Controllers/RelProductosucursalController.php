<?php

namespace App\Http\Controllers;

use App\Models\RelProductosucursal;
use App\Http\Requests\RelProductosucursalRequest;

/**
 * Class RelProductosucursalController
 * @package App\Http\Controllers
 */
class RelProductosucursalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $relProductosucursals = RelProductosucursal::paginate();

        return view('rel-productosucursal.index', compact('relProductosucursals'))
            ->with('i', (request()->input('page', 1) - 1) * $relProductosucursals->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $relProductosucursal = new RelProductosucursal();
        return view('rel-productosucursal.create', compact('relProductosucursal'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RelProductosucursalRequest $request)
    {
        RelProductosucursal::create($request->validated());

        return redirect()->route('rel-productosucursals.index')
            ->with('success', 'RelProductosucursal created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $relProductosucursal = RelProductosucursal::find($id);

        return view('rel-productosucursal.show', compact('relProductosucursal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $relProductosucursal = RelProductosucursal::find($id);

        return view('rel-productosucursal.edit', compact('relProductosucursal'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RelProductosucursalRequest $request, RelProductosucursal $relProductosucursal)
    {
        $relProductosucursal->update($request->validated());

        return redirect()->route('rel-productosucursals.index')
            ->with('success', 'RelProductosucursal updated successfully');
    }

    public function destroy($id)
    {
        RelProductosucursal::find($id)->delete();

        return redirect()->route('rel-productosucursals.index')
            ->with('success', 'RelProductosucursal deleted successfully');
    }
}
