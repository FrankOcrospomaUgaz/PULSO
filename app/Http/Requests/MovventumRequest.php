<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MovventumRequest extends FormRequest
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
			'json_sucursal' => 'required',
			'serie' => 'required|string',
			'anio' => 'required|string',
			'tipo_detalle' => 'required|string',
			'consumo' => 'required|string',
			'tipo_pago' => 'required|string',
			'estado' => 'required|string',
			'tipo_venta' => 'required|string',
			'moneda' => 'required|string',
			'tipocambio' => 'required',
			'subtotal' => 'required',
			'igv' => 'required',
			'total' => 'required',
			'movimiento_id' => 'required',
			'sucursal_id' => 'required',
        ];
    }
}
