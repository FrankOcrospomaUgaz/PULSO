<?php

namespace App\Http\Controllers;

use App\Models\Dinero;
use App\Http\Requests\DineroRequest;

/**
 * Class DineroController
 * @package App\Http\Controllers
 */
class DineroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dineros = Dinero::paginate();

        return view('dinero.index', compact('dineros'))
            ->with('i', (request()->input('page', 1) - 1) * $dineros->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $dinero = new Dinero();
        return view('dinero.create', compact('dinero'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DineroRequest $request)
    {
        Dinero::create($request->validated());

        return redirect()->route('dineros.index')
            ->with('success', 'Dinero created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $dinero = Dinero::find($id);

        return view('dinero.show', compact('dinero'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $dinero = Dinero::find($id);

        return view('dinero.edit', compact('dinero'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DineroRequest $request, Dinero $dinero)
    {
        $dinero->update($request->validated());

        return redirect()->route('dineros.index')
            ->with('success', 'Dinero updated successfully');
    }

    public function destroy($id)
    {
        Dinero::find($id)->delete();

        return redirect()->route('dineros.index')
            ->with('success', 'Dinero deleted successfully');
    }
}
