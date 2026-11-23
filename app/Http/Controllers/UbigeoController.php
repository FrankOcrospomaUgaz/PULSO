<?php

namespace App\Http\Controllers;

use App\Models\Ubigeo;
use App\Http\Requests\UbigeoRequest;
use Illuminate\Http\Request;

/**
 * Class UbigeoController
 * @package App\Http\Controllers
 */
class UbigeoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    $search = $request->input('search');
    $perPage = $request->input('perPage', 10); // valor por defecto

    $query = Ubigeo::query();

    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('descripcion', 'ILIKE', "%{$search}%")
              ->orWhere('codigo', 'ILIKE', "%{$search}%")
              ->orWhere('inei', 'ILIKE', "%{$search}%")
              ->orWhere('tipo', 'ILIKE', "%{$search}%");
        });
    }

    $ubigeos = $query->paginate($perPage)->appends([
        'search' => $search,
        'perPage' => $perPage,
    ]);

    return view('ubigeo.index', compact('ubigeos', 'search', 'perPage'))
        ->with('i', (request()->input('page', 1) - 1) * $ubigeos->perPage());
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $ubigeo = new Ubigeo();
        return view('ubigeo.create', compact('ubigeo'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UbigeoRequest $request)
    {
        Ubigeo::create($request->validated());

        return redirect()->route('ubigeos.index')
            ->with('success', 'Ubigeo created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $ubigeo = Ubigeo::find($id);

        return view('ubigeo.show', compact('ubigeo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $ubigeo = Ubigeo::find($id);

        return view('ubigeo.edit', compact('ubigeo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UbigeoRequest $request, Ubigeo $ubigeo)
    {
        $ubigeo->update($request->validated());

        return redirect()->route('ubigeos.index')
            ->with('success', 'Ubigeo updated successfully');
    }

    public function destroy($id)
    {
        Ubigeo::find($id)->delete();

        return redirect()->route('ubigeos.index')
            ->with('success', 'Ubigeo deleted successfully');
    }
}
