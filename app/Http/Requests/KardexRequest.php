<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KardexRequest extends FormRequest
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
			'producto_id' => 'required',
			'json_producto' => 'required',
			'unidad_id' => 'required',
			'json_listaunidad' => 'required',
			'cantidad' => 'required',
			'unidadbase_id' => 'required',
			'cantidadbase' => 'required',
			'preciounitario' => 'required',
			'moneda' => 'required|string',
			'tipocambio' => 'required',
			'total' => 'required',
			'fecha' => 'required',
			'situacion' => 'required|string',
			'usuario_id' => 'required',
			'usuario' => 'required|string',
			'movimiento_id' => 'required',
			'tipomovimiento_id' => 'required',
			'tipodocumento_id' => 'required',
			'sucursal_id' => 'required',
			'stockanterior' => 'required',
			'stockactual' => 'required',
        ];
    }
}
