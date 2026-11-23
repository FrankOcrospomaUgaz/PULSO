<?php

namespace App\Http\Controllers;

use App\Models\Origenreserva;
use App\Models\Operacione;
use App\Models\Persona;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrigenReservaController extends Controller
{
    /**
     * Muestra la lista de orígenes de reserva.
     */
    public function index(Request $request)
    {
        $search = $request->input('search'); // Buscar por descripción
        $perPage = $request->input('perPage', 10);
        $query = Origenreserva::where('situacion', true); // Filtrar solo activos

        if ($search) {
            $query->where('descripcion', 'ILIKE', "%$search%");
        }

        $origenesreserva = $query->orderBy('id', 'asc')->paginate( $perPage);
        $operaciones = Operacione::where('tabla_id', 78)->where('estado', true)->get(); // Ajusta `tabla_id` si es diferente

        return view('origenreserva.index', compact('origenesreserva', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $origenesreserva->perPage());
    }

    /**
     * Muestra el formulario para crear un nuevo origen de reserva.
     */
    public function create()
    {
        $origen = new OrigenReserva();
        return view('origenreserva.create', compact('origen'));
    }

    /**
     * Guarda un nuevo origen de reserva en la base de datos.
     */

     public function store(Request $request)
     {
         try {
             $request->validate([
                 'descripcion' => 'required|string',
                 'color' => 'required|string|max:7', // HEX
                 'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048' // Valida la imagen
             ]);
     
             $user = Auth::user();
             $persona = Persona::find($user->persona_id);
             $sucursal = Sucursal::find($persona->sucursal_id);
             $sucursal_id = $sucursal->id ?? null;
     
             $base64Logo = null;
     
             if ($request->hasFile('logo')) {
                 $image = $request->file('logo');
                 $base64Logo = base64_encode(file_get_contents($image->getRealPath())); // Convertir a Base64
             }
     
             Origenreserva::create([
                 'descripcion' => $request->descripcion,
                 'color' => $request->color,
                 'sucursal_id' => $sucursal_id,
                 'logo' => $base64Logo
             ]);
     
             return response()->json([
                 'success' => true,
                 'message' => 'Origen de reserva creado exitosamente.',
                 'redirect_url' => route('origenreserva.index')
             ]);
         } catch (\Exception $e) {
             return response()->json([
                 'success' => false,
                 'errors' => $e->getMessage()
             ], 500);
         }
     }
    
    /**
     * Muestra un origen de reserva específico.
     */
    public function show($id)
    {
        $origen = Origenreserva::findOrFail($id);
        return view('origenreserva.show', compact('origen'));
    }

    /**
     * Muestra el formulario para editar un origen de reserva.
     */
    public function edit($id)
    {
        $origen = Origenreserva::findOrFail($id);
        return view('origenreserva.edit', compact('origen'));
    }

    /**
     * Actualiza un origen de reserva en la base de datos.
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'descripcion' => "required|string|max:255|unique:origenreserva,descripcion,$id",
                'color' => 'required|string|max:7', // Asegura que sea un código HEX válido con #
                'situacion' => 'boolean',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048' // Valida la imagen opcionalmente
            ]);
    
            $origen = Origenreserva::findOrFail($id);
    
            $user = Auth::user();
            $persona = Persona::find($user->persona_id);
            $sucursal = Sucursal::find($persona->sucursal_id);
            $sucursal_id = $sucursal->id ?? null;
    
            // Si el usuario sube un nuevo logo, se convierte a Base64
            if ($request->hasFile('logo')) {
                $image = $request->file('logo');
                $base64Logo = base64_encode(file_get_contents($image->getRealPath()));
            } else {
                $base64Logo = $origen->logo; // Mantener el logo anterior si no se sube uno nuevo
            }
    
            $origen->update([
                'descripcion' => $request->descripcion,
                'color' => $request->color,
                'situacion' => $request->situacion ?? $origen->situacion,
                'sucursal_id' => $sucursal_id,
                'logo' => $base64Logo
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Origen de reserva actualizado correctamente.',
                'redirect_url' => route('origenreserva.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
    
    

    /**
     * Elimina un origen de reserva (soft delete).
     */
    public function destroy($id)
    {
        try {
        
            $origen = Origenreserva::findOrFail($id);
            $origen->delete(); // Soft delete
    
            return response()->json([
                'success' => true,
                'message' => 'Origen eliminado correctamente.',
                'redirect_url' => route('origenreserva.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Recupera un origen de reserva eliminado (soft delete).
     */
    public function restore($id)
    {
        $origen = Origenreserva::onlyTrashed()->findOrFail($id);
        $origen->restore();

        return redirect()->route('origenreserva.index')
            ->with('success', 'Origen de reserva restaurado correctamente.');
    }
}
