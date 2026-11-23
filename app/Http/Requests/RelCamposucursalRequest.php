<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RelCamposucursalRequest extends FormRequest
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
			'descripcion' => 'required|string',
			'tipo' => 'required|string',
			'orden' => 'required',
			'diccionario' => 'required|string',
			'situacion' => 'required|string',
			'campo_id' => 'required',
			'tabla_id' => 'required',
			'sucursal_id' => 'required',
        ];
    }
}
