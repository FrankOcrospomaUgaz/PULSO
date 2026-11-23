<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductoRequest extends FormRequest
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
			'codigo' => 'required|string',
			'descripcion' => 'required|string',
			'abreviatura' => 'required|string',
			'tipo' => 'required|string',
			'categoria_id' => 'required',
			'unidadbase_id' => 'required',
			// 'kardex' => 'required|string',
			// 'compuesto' => 'required|string',
			// 'autoproduccion' => 'required|string',
			'peso' => 'required',
			'comentario' => 'required|string',
			// 'imagen' => 'required|string',
			'complemento' => 'required|string',
			// 'modocomplemento' => 'required|string',
			'empresa_id' => 'required',
			'clasificacion' => 'required|string',
			'nro_parte' => 'required|string',
			'caracteristicas' => 'required|string',
			// 'imagen2' => 'required|string',
			// 'imagen3' => 'required|string',
        ];
    }
}
