<?php

namespace App\Http\Controllers;

use App\Models\DetMovventum;
use App\Http\Requests\DetMovventumRequest;

/**
 * Class DetMovventumController
 * @package App\Http\Controllers
 */
class DetMovventumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $detMovventa = DetMovventum::paginate();

        return view('det-movventum.index', compact('detMovventa'))
            ->with('i', (request()->input('page', 1) - 1) * $detMovventa->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $detMovventum = new DetMovventum();
        return view('det-movventum.create', compact('detMovventum'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DetMovventumRequest $request)
    {
        DetMovventum::create($request->validated());

        return redirect()->route('det-movventa.index')
            ->with('success', 'DetMovventum created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $detMovventum = DetMovventum::find($id);

        return view('det-movventum.show', compact('detMovventum'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $detMovventum = DetMovventum::find($id);

        return view('det-movventum.edit', compact('detMovventum'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DetMovventumRequest $request, DetMovventum $detMovventum)
    {
        $detMovventum->update($request->validated());

        return redirect()->route('det-movventa.index')
            ->with('success', 'DetMovventum updated successfully');
    }

    public function destroy($id)
    {
        DetMovventum::find($id)->delete();

        return redirect()->route('det-movventa.index')
            ->with('success', 'DetMovventum deleted successfully');
    }
}
