<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Si la opción es "nombre", hacer que 'tipopersona' y 'nrodoc' no sean obligatorios
        if ($this->input('opcion') == 'nombre') {
            return [
                'nombres' => 'required|string',
                // 'apellidos' => '',
                // 'direccion' => 'required|string',
                //'email' => 'required|string|email',
                // 'telefonofijo' => 'required|string',
                // 'telefonomovil' => 'required|string',
                'imagen' => 'nullable|string',
                'compartido' => 'nullable|string',
                'sucursal_id' => 'required',
                // 'distrito_id' => 'required',
                // Estos campos no son obligatorios si la opción es 'nombre'
                'tipopersona' => 'nullable|string',
                'nrodoc' => 'nullable|string',
                'sexo' => 'nullable|string',
                'fechanac' => 'nullable|date',
            ];
        } else {
            // Regla para cuando se selecciona 'documento'
            return [
                'nombres' => 'required|string',
                'apellidos' => '',
                // 'direccion' => 'required|string',
                //'email' => 'required|string|email',
                // 'telefonofijo' => 'required|string',
                // 'telefonomovil' => 'required|string',
                'imagen' => 'nullable|string',
                'compartido' => 'nullable|string',
                'sucursal_id' => 'required',
                // 'distrito_id' => 'required',
                // Reglas adicionales para personamaestro
                'tipopersona' => 'required|string',
                'nrodoc' => 'required|string',
                'sexo' => 'required|string',
                'fechanac' => 'nullable|date',
            ];
        }
    }
    
    
}
