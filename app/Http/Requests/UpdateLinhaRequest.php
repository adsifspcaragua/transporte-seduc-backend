<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLinhaRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'string|max:255|min:3',
            'description' => 'sometimes|string|max:255|min:3',
            'departure_time' =>'sometimes|date_format:H:i',
            'return_time' => 'sometimes|date_format:H:i|after:departure_time',
            'max_capacity' => 'required|integer',
        ];
    }
}
