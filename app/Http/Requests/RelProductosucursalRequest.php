<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RelProductosucursalRequest extends FormRequest
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
			'situacion' => 'required|string',
			'stockminimo' => 'required',
			'stockmaximo' => 'required',
			'stockoptimo' => 'required',
			'minimovender' => 'required',
			'minimocomprar' => 'required',
			'fila' => 'required',
			'columna' => 'required',
			'favorito' => 'required|string',
			'venta_unitaria' => 'required|string',
			'producto_id' => 'required',
			'sucursal_id' => 'required',
			'duracion' => 'required',
        ];
    }
}
