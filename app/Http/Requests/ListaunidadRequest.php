<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListaunidadRequest extends FormRequest
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
			'formula' => 'required',
			'producto_id' => 'required',
			'unidad_id' => 'required',
			'unidadbase_id' => 'required',
			'empresa_id' => 'required',
        ];
    }
}
