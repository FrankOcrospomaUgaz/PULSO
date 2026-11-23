<?php

namespace App\Http\Controllers;

use App\Models\Tipopreguntum;
use App\Http\Requests\TipopreguntumRequest;

/**
 * Class TipopreguntumController
 * @package App\Http\Controllers
 */
class TipopreguntumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tipopregunta = Tipopreguntum::paginate();

        return view('tipopreguntum.index', compact('tipopregunta'))
            ->with('i', (request()->input('page', 1) - 1) * $tipopregunta->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tipopreguntum = new Tipopreguntum();
        return view('tipopreguntum.create', compact('tipopreguntum'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TipopreguntumRequest $request)
    {
        Tipopreguntum::create($request->validated());

        return redirect()->route('tipopregunta.index')
            ->with('success', 'Tipopreguntum created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $tipopreguntum = Tipopreguntum::find($id);

        return view('tipopreguntum.show', compact('tipopreguntum'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $tipopreguntum = Tipopreguntum::find($id);

        return view('tipopreguntum.edit', compact('tipopreguntum'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TipopreguntumRequest $request, Tipopreguntum $tipopreguntum)
    {
        $tipopreguntum->update($request->validated());

        return redirect()->route('tipopregunta.index')
            ->with('success', 'Tipopreguntum updated successfully');
    }

    public function destroy($id)
    {
        Tipopreguntum::find($id)->delete();

        return redirect()->route('tipopregunta.index')
            ->with('success', 'Tipopreguntum deleted successfully');
    }
}
