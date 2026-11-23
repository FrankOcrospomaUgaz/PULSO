<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MovreservaRequest extends FormRequest
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
			'dias' => 'required',
			'total' => 'required',
			'motivo_id' => 'required',

        ];
    }
}
