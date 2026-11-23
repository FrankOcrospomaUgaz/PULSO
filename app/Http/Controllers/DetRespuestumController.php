<?php

namespace App\Http\Controllers;

use App\Models\DetRespuestum;
use App\Http\Requests\DetRespuestumRequest;

/**
 * Class DetRespuestumController
 * @package App\Http\Controllers
 */
class DetRespuestumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $detRespuesta = DetRespuestum::paginate();

        return view('det-respuestum.index', compact('detRespuesta'))
            ->with('i', (request()->input('page', 1) - 1) * $detRespuesta->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $detRespuestum = new DetRespuestum();
        return view('det-respuestum.create', compact('detRespuestum'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DetRespuestumRequest $request)
    {
        DetRespuestum::create($request->validated());

        return redirect()->route('det-respuesta.index')
            ->with('success', 'DetRespuestum created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $detRespuestum = DetRespuestum::find($id);

        return view('det-respuestum.show', compact('detRespuestum'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $detRespuestum = DetRespuestum::find($id);

        return view('det-respuestum.edit', compact('detRespuestum'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DetRespuestumRequest $request, DetRespuestum $detRespuestum)
    {
        $detRespuestum->update($request->validated());

        return redirect()->route('det-respuesta.index')
            ->with('success', 'DetRespuestum updated successfully');
    }

    public function destroy($id)
    {
        DetRespuestum::find($id)->delete();

        return redirect()->route('det-respuesta.index')
            ->with('success', 'DetRespuestum deleted successfully');
    }
}
