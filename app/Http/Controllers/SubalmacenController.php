<?php

namespace App\Http\Controllers;

use App\Models\Subalmacen;
use App\Http\Requests\SubalmacenRequest;

/**
 * Class SubalmacenController
 * @package App\Http\Controllers
 */
class SubalmacenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subalmacens = Subalmacen::paginate();

        return view('subalmacen.index', compact('subalmacens'))
            ->with('i', (request()->input('page', 1) - 1) * $subalmacens->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $subalmacen = new Subalmacen();
        return view('subalmacen.create', compact('subalmacen'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SubalmacenRequest $request)
    {
        Subalmacen::create($request->validated());

        return redirect()->route('subalmacens.index')
            ->with('success', 'Subalmacen created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $subalmacen = Subalmacen::find($id);

        return view('subalmacen.show', compact('subalmacen'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $subalmacen = Subalmacen::find($id);

        return view('subalmacen.edit', compact('subalmacen'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SubalmacenRequest $request, Subalmacen $subalmacen)
    {
        $subalmacen->update($request->validated());

        return redirect()->route('subalmacens.index')
            ->with('success', 'Subalmacen updated successfully');
    }

    public function destroy($id)
    {
        Subalmacen::find($id)->delete();

        return redirect()->route('subalmacens.index')
            ->with('success', 'Subalmacen deleted successfully');
    }
}
