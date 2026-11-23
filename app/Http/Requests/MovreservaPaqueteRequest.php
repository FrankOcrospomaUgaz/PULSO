<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MovreservaPaqueteRequest extends FormRequest
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
			'observacion' => 'string',
			'nropersonas' => 'required',
			'fechavencimiento' => 'required',
			'precio_paquete' => 'required',
			'total' => 'required',
			'salon_package_name' => 'required|string',
			'estado' => 'required|string',
			'movimiento_id' => 'required',
			'sucursal_id' => 'required',
			'celular' => 'string',
			'email' => 'string',
        ];
    }
}
