<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEstudanteRequest extends FormRequest
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
            "name" => "required|string|max:255",
            "email" => "required|email|unique:estudantes,email",
            "cpf" => "string|min:11|max:11|unique:estudantes,cpf",
            "birth_date" => "date|before:today|date_format:Y-m-d",
            'phone' => 'required|string|max:15',
            'address' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'days_of_week' => 'required|array',
            'days_of_week.*' => 'string',
            'observation' => 'nullable|string|max:1000',
            'status' => 'prohibited',
            'linha_id' => 'nullable|integer',
            'instituicao_id' => 'required|string|exists:instituicoes,id',
            'inscricao_id' => 'required|string|exists:inscricoes,id|unique:estudantes,inscricao_id',
            'user_id' => 'nullable|integer|exists:users,id|unique:estudantes,user_id,'
        ];
    }
}


