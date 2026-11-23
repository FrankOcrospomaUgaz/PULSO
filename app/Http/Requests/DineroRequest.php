<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DineroRequest extends FormRequest
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
			'valor' => 'required',
			'tipo' => 'required|string',
			'descripcion' => 'required|string',
			'orden' => 'required',
			'situacion' => 'required|string',
        ];
    }
}
