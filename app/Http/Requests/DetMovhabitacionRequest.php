<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DetMovhabitacionRequest extends FormRequest
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
			'tipo_detalle' => 'required|string',
			'movhabitacion_id' => 'required',
			'codigo' => 'required|string',
			'descripcion' => 'required|string',
			'unidad_id' => 'required',
			'json_unidad' => 'required',
			'cantidad' => 'required',
			'monto' => 'required',
			'dcto_porcentaje' => 'required',
			'monto_original' => 'required',
			'comentario' => 'required|string',
			'det_categoria' => 'required',
			'opc_categoria' => 'required',
			'complementos' => 'required',
			'situacion' => 'required|string',
			'sucursal_id' => 'required',
        ];
    }
}
