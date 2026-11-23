<?php

namespace App\Http\Controllers;

use App\Models\RelOperacionsucursal;
use App\Http\Requests\RelOperacionsucursalRequest;

/**
 * Class RelOperacionsucursalController
 * @package App\Http\Controllers
 */
class RelOperacionsucursalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $relOperacionsucursals = RelOperacionsucursal::paginate();

        return view('rel-operacionsucursal.index', compact('relOperacionsucursals'))
            ->with('i', (request()->input('page', 1) - 1) * $relOperacionsucursals->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $relOperacionsucursal = new RelOperacionsucursal();
        return view('rel-operacionsucursal.create', compact('relOperacionsucursal'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RelOperacionsucursalRequest $request)
    {
        RelOperacionsucursal::create($request->validated());

        return redirect()->route('rel-operacionsucursals.index')
            ->with('success', 'RelOperacionsucursal created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $relOperacionsucursal = RelOperacionsucursal::find($id);

        return view('rel-operacionsucursal.show', compact('relOperacionsucursal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $relOperacionsucursal = RelOperacionsucursal::find($id);

        return view('rel-operacionsucursal.edit', compact('relOperacionsucursal'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RelOperacionsucursalRequest $request, RelOperacionsucursal $relOperacionsucursal)
    {
        $relOperacionsucursal->update($request->validated());

        return redirect()->route('rel-operacionsucursals.index')
            ->with('success', 'RelOperacionsucursal updated successfully');
    }

    public function destroy($id)
    {
        RelOperacionsucursal::find($id)->delete();

        return redirect()->route('rel-operacionsucursals.index')
            ->with('success', 'RelOperacionsucursal deleted successfully');
    }
}
