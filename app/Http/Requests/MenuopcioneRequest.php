<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MenuopcioneRequest extends FormRequest
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
            'accion' => 'required|string',
			'tabla_id' => 'required',
			'modulo_id' => 'required',
			'tipoopcion_id' => 'required',
            'icono' => 'string',
        ];
    }
}
