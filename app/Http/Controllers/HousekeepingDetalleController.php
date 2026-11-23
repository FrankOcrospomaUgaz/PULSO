<?php

namespace App\Http\Controllers;

use App\Models\HousekeepingDetalle;
use App\Http\Requests\HousekeepingDetalleRequest;

/**
 * Class HousekeepingDetalleController
 * @package App\Http\Controllers
 */
class HousekeepingDetalleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $housekeepingDetalles = HousekeepingDetalle::paginate();

        return view('housekeeping-detalle.index', compact('housekeepingDetalles'))
            ->with('i', (request()->input('page', 1) - 1) * $housekeepingDetalles->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $housekeepingDetalle = new HousekeepingDetalle();
        return view('housekeeping-detalle.create', compact('housekeepingDetalle'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HousekeepingDetalleRequest $request)
    {
        HousekeepingDetalle::create($request->validated());

        return redirect()->route('housekeeping-detalles.index')
            ->with('success', 'HousekeepingDetalle created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $housekeepingDetalle = HousekeepingDetalle::find($id);

        return view('housekeeping-detalle.show', compact('housekeepingDetalle'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $housekeepingDetalle = HousekeepingDetalle::find($id);

        return view('housekeeping-detalle.edit', compact('housekeepingDetalle'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HousekeepingDetalleRequest $request, HousekeepingDetalle $housekeepingDetalle)
    {
        $housekeepingDetalle->update($request->validated());

        return redirect()->route('housekeeping-detalles.index')
            ->with('success', 'HousekeepingDetalle updated successfully');
    }

    public function destroy($id)
    {
        HousekeepingDetalle::find($id)->delete();

        return redirect()->route('housekeeping-detalles.index')
            ->with('success', 'HousekeepingDetalle deleted successfully');
    }
}
