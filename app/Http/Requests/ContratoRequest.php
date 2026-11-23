<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContratoRequest extends FormRequest
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
        return [
			'numero_contrato' => 'required|string',
			'fecha_inicio' => 'required',
			'fecha_fin' => 'required',
			'dia_pago' => 'required',
			'monto' => 'required',
			'garantia' => 'required',
			'cubre_luz' => 'required|boolean',
			'cubre_agua' => 'required|boolean',
			'descripcion' => 'string',
			'informacion_adicional' => 'string',
			'ambiente_id' => 'required',
			'personamaestro_id' => 'required',
			'edificio_id' => 'required',
        ];
    }
}
