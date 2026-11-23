<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DetMovalmacenRequest extends FormRequest
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
			'movalmacen_id' => 'required',
			'producto_id' => 'required',
			'json_producto' => 'required',
			'unidad_id' => 'required',
			'json_listaunidad' => 'required',
			'cantidad' => 'required',
			'comentario' => 'required|string',
			'situacion' => 'required|string',
			'sucursal_id' => 'required',
        ];
    }
}
