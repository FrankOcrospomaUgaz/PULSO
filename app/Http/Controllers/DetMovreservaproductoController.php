<?php

namespace App\Http\Controllers;

use App\Models\DetMovreservaproducto;
use App\Http\Requests\DetMovreservaproductoRequest;

/**
 * Class DetMovreservaproductoController
 * @package App\Http\Controllers
 */
class DetMovreservaproductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $detMovreservaproductos = DetMovreservaproducto::paginate();

        return view('det-movreservaproducto.index', compact('detMovreservaproductos'))
            ->with('i', (request()->input('page', 1) - 1) * $detMovreservaproductos->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $detMovreservaproducto = new DetMovreservaproducto();
        return view('det-movreservaproducto.create', compact('detMovreservaproducto'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DetMovreservaproductoRequest $request)
    {
        DetMovreservaproducto::create($request->validated());

        return redirect()->route('det-movreservaproductos.index')
            ->with('success', 'DetMovreservaproducto created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $detMovreservaproducto = DetMovreservaproducto::find($id);

        return view('det-movreservaproducto.show', compact('detMovreservaproducto'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $detMovreservaproducto = DetMovreservaproducto::find($id);

        return view('det-movreservaproducto.edit', compact('detMovreservaproducto'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DetMovreservaproductoRequest $request, DetMovreservaproducto $detMovreservaproducto)
    {
        $detMovreservaproducto->update($request->validated());

        return redirect()->route('det-movreservaproductos.index')
            ->with('success', 'DetMovreservaproducto updated successfully');
    }

    public function destroy($id)
    {
        DetMovreservaproducto::find($id)->delete();

        return redirect()->route('det-movreservaproductos.index')
            ->with('success', 'DetMovreservaproducto deleted successfully');
    }
}
