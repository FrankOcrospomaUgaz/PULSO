<?php

namespace App\Http\Controllers;

use App\Models\ValordetRespuestum;
use App\Http\Requests\ValordetRespuestumRequest;

/**
 * Class ValordetRespuestumController
 * @package App\Http\Controllers
 */
class ValordetRespuestumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $valordetRespuesta = ValordetRespuestum::paginate();

        return view('valordet-respuestum.index', compact('valordetRespuesta'))
            ->with('i', (request()->input('page', 1) - 1) * $valordetRespuesta->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $valordetRespuestum = new ValordetRespuestum();
        return view('valordet-respuestum.create', compact('valordetRespuestum'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ValordetRespuestumRequest $request)
    {
        ValordetRespuestum::create($request->validated());

        return redirect()->route('valordet-respuesta.index')
            ->with('success', 'ValordetRespuestum created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $valordetRespuestum = ValordetRespuestum::find($id);

        return view('valordet-respuestum.show', compact('valordetRespuestum'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $valordetRespuestum = ValordetRespuestum::find($id);

        return view('valordet-respuestum.edit', compact('valordetRespuestum'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ValordetRespuestumRequest $request, ValordetRespuestum $valordetRespuestum)
    {
        $valordetRespuestum->update($request->validated());

        return redirect()->route('valordet-respuesta.index')
            ->with('success', 'ValordetRespuestum updated successfully');
    }

    public function destroy($id)
    {
        ValordetRespuestum::find($id)->delete();

        return redirect()->route('valordet-respuesta.index')
            ->with('success', 'ValordetRespuestum deleted successfully');
    }
}
