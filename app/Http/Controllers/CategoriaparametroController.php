<?php

namespace App\Http\Controllers;

use App\Models\Categoriaparametro;
use App\Http\Requests\CategoriaparametroRequest;
use App\Models\Operacione;

/**
 * Class CategoriaparametroController
 * @package App\Http\Controllers
 */
class CategoriaparametroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categoriaparametros = Categoriaparametro::paginate();
        $operaciones = Operacione::where('tabla_id', 15)->where('estado',true)->get();

        return view('categoriaparametro.index', compact('categoriaparametros', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $categoriaparametros->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categoriaparametro = new Categoriaparametro();
        return view('categoriaparametro.create', compact('categoriaparametro'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoriaparametroRequest $request)
    {
        Categoriaparametro::create($request->validated());

        return redirect()->route('categoriaparametros.index')
            ->with('success', 'Categoriaparametro created successfully.');
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $categoriaparametro = Categoriaparametro::find($id);

        return view('categoriaparametro.edit', compact('categoriaparametro'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoriaparametroRequest $request, Categoriaparametro $categoriaparametro)
    {
        $categoriaparametro->update($request->validated());

        return redirect()->route('categoriaparametros.index')
            ->with('success', 'Categoriaparametro updated successfully');
    }

    public function destroy($id)
    {
        Categoriaparametro::find($id)->delete();

        return redirect()->route('categoriaparametros.index')
            ->with('success', 'Categoriaparametro deleted successfully');
    }
        /**
     * Move the order of the menu option.
     */
    public function moveOrder($id, $direction)
    {
        try {
            $categoriaparametro = Categoriaparametro::find($id);

            if (!$categoriaparametro) {
                return response()->json(['success' => false, 'message' => 'Menú opción no encontrada.']);
            }

            if ($direction === 'Bajar') {
                $otherMenuopcion = Categoriaparametro::where('orden', '<', $categoriaparametro->orden)
                    ->orderBy('orden', 'desc')
                    ->first();

                if ($otherMenuopcion) {
                    $this->swapOrder($categoriaparametro, $otherMenuopcion);
                }
            } else if ($direction === 'Subir') {
                $otherMenuopcion = Categoriaparametro::where('orden', '>', $categoriaparametro->orden)
                    ->orderBy('orden', 'asc')
                    ->first();

                if ($otherMenuopcion) {
                    $this->swapOrder($categoriaparametro, $otherMenuopcion);
                }
            }

            // Redireccionar a la vista con la URL para recargar dinámicamente
            return response()->json([
                'success' => true,
                'redirect_url' => route('categoriaparametros.index')
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
        private function swapOrder($menuopcion1, $menuopcion2)
    {
        $tempOrder = $menuopcion1->orden;
        $menuopcion1->orden = $menuopcion2->orden;
        $menuopcion2->orden = $tempOrder;

        $menuopcion1->save();
        $menuopcion2->save();
    }
}
