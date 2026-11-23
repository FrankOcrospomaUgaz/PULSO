<?php

namespace App\Http\Controllers;

use App\Models\DetMovcaja;
use App\Http\Requests\DetMovcajaRequest;

/**
 * Class DetMovcajaController
 * @package App\Http\Controllers
 */
class DetMovcajaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $detMovcajas = DetMovcaja::paginate();

        return view('det-movcaja.index', compact('detMovcajas'))
            ->with('i', (request()->input('page', 1) - 1) * $detMovcajas->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $detMovcaja = new DetMovcaja();
        return view('det-movcaja.create', compact('detMovcaja'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DetMovcajaRequest $request)
    {
        DetMovcaja::create($request->validated());

        return redirect()->route('det-movcajas.index')
            ->with('success', 'DetMovcaja created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $detMovcaja = DetMovcaja::find($id);

        return view('det-movcaja.show', compact('detMovcaja'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $detMovcaja = DetMovcaja::find($id);

        return view('det-movcaja.edit', compact('detMovcaja'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DetMovcajaRequest $request, DetMovcaja $detMovcaja)
    {
        $detMovcaja->update($request->validated());

        return redirect()->route('det-movcajas.index')
            ->with('success', 'DetMovcaja updated successfully');
    }

    public function destroy($id)
    {
        DetMovcaja::find($id)->delete();

        return redirect()->route('det-movcajas.index')
            ->with('success', 'DetMovcaja deleted successfully');
    }
}
