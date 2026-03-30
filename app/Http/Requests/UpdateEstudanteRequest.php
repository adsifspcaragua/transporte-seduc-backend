<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEstudanteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
          return [
            "name" => "required|string|max:255",
            "email" => "required|email|unique:estudantes,email," . $this->route('estudante')?->id,
            "cpf" => "string|min:11|max:11|unique:estudantes,cpf," . $this->route('estudante')?->id,
            "birth_date" => "date|before:today|date_format:Y-m-d",
            'rg' => 'required|string|min:8|max:11',
            'phone' => 'required|string|max:15',
            'address' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'course' => 'required|string|max:255',
            'semester' => 'required|integer|min:1|max:20',
            'year_completion' => 'required|string|max:4',
            'instituicao_id' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'days_of_week' => 'required|array',
            'days_of_week.*' => 'string',
            'has_scholarship' => 'required|boolean',
            'scholarship_type' => 'nullable|string|max:255',
            'observation' => 'nullable|string|max:1000',
        ];
    }
}
