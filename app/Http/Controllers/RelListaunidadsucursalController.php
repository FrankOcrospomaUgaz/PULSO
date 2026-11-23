<?php

namespace App\Http\Controllers;

use App\Models\RelListaunidadsucursal;
use App\Http\Requests\RelListaunidadsucursalRequest;

/**
 * Class RelListaunidadsucursalController
 * @package App\Http\Controllers
 */
class RelListaunidadsucursalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $relListaunidadsucursals = RelListaunidadsucursal::paginate();

        return view('rel-listaunidadsucursal.index', compact('relListaunidadsucursals'))
            ->with('i', (request()->input('page', 1) - 1) * $relListaunidadsucursals->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $relListaunidadsucursal = new RelListaunidadsucursal();
        return view('rel-listaunidadsucursal.create', compact('relListaunidadsucursal'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RelListaunidadsucursalRequest $request)
    {
        RelListaunidadsucursal::create($request->validated());

        return redirect()->route('rel-listaunidadsucursals.index')
            ->with('success', 'RelListaunidadsucursal created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $relListaunidadsucursal = RelListaunidadsucursal::find($id);

        return view('rel-listaunidadsucursal.show', compact('relListaunidadsucursal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $relListaunidadsucursal = RelListaunidadsucursal::find($id);

        return view('rel-listaunidadsucursal.edit', compact('relListaunidadsucursal'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RelListaunidadsucursalRequest $request, RelListaunidadsucursal $relListaunidadsucursal)
    {
        $relListaunidadsucursal->update($request->validated());

        return redirect()->route('rel-listaunidadsucursals.index')
            ->with('success', 'RelListaunidadsucursal updated successfully');
    }

    public function destroy($id)
    {
        RelListaunidadsucursal::find($id)->delete();

        return redirect()->route('rel-listaunidadsucursals.index')
            ->with('success', 'RelListaunidadsucursal deleted successfully');
    }
}
