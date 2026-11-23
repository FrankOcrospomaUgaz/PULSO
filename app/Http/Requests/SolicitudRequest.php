<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SolicitudRequest extends FormRequest
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
			'tipo_solicitud' => 'required|string',
			'data' => 'required',
			'fecha' => 'required',
			'estado' => 'required|string',
			'nombre' => 'string',
			'moneda' => 'string',
			'tipo_documento' => 'required|string',
			'serie' => 'required|string',
			'correlativo' => 'required',
			'ticket_solicitud' => 'string',
			'sucursal_id' => 'required',
			'json_sucursal' => 'required',
			'facturador_id' => 'required',
			'json_facturador' => 'required',
			'error_text' => 'string',
			'public_name' => 'string',
        ];
    }
}
