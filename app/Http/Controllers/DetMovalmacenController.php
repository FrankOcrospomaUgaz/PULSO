<?php

namespace App\Http\Controllers;

use App\Models\DetMovalmacen;
use App\Http\Requests\DetMovalmacenRequest;

/**
 * Class DetMovalmacenController
 * @package App\Http\Controllers
 */
class DetMovalmacenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $detMovalmacens = DetMovalmacen::paginate();

        return view('det-movalmacen.index', compact('detMovalmacens'))
            ->with('i', (request()->input('page', 1) - 1) * $detMovalmacens->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $detMovalmacen = new DetMovalmacen();
        return view('det-movalmacen.create', compact('detMovalmacen'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DetMovalmacenRequest $request)
    {
        DetMovalmacen::create($request->validated());

        return redirect()->route('det-movalmacens.index')
            ->with('success', 'DetMovalmacen created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $detMovalmacen = DetMovalmacen::find($id);

        return view('det-movalmacen.show', compact('detMovalmacen'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $detMovalmacen = DetMovalmacen::find($id);

        return view('det-movalmacen.edit', compact('detMovalmacen'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DetMovalmacenRequest $request, DetMovalmacen $detMovalmacen)
    {
        $detMovalmacen->update($request->validated());

        return redirect()->route('det-movalmacens.index')
            ->with('success', 'DetMovalmacen updated successfully');
    }

    public function destroy($id)
    {
        DetMovalmacen::find($id)->delete();

        return redirect()->route('det-movalmacens.index')
            ->with('success', 'DetMovalmacen deleted successfully');
    }
}
