<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FacturadorRequest extends FormRequest
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
			'empresa_facturador_id' => 'required',
			'porcentaje_igv' => 'required',
			'username_wsdl' => 'required|string',
			'password_wsdl' => 'required|string',
			'modo_autenticacion' => 'required|string',
			'username_sunat' => 'required|string',
			'password_sunat' => 'required|string',
			'sucursal_id' => 'required',
        ];
    }
}
