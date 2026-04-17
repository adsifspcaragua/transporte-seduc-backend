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
            'phone' => 'required|string|max:15|unique:estudantes,phone',
            'address' => 'required|string|max:255',
            'instituicao_id' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'days_of_week' => 'required|array',
            'days_of_week.*' => 'string',
            'observation' => 'nullable|string|max:1000',
            'status' => 'required|string|max:255',
            'linha_id' => 'nullable|integer',
            'instituicao_id' => 'required|string|exists:instituicoes,id',
            'inscricao_id' => 'required|string|exists:inscricoes,id|unique:estudantes,inscricao_id',
            'user_id' => 'nullable|integer|exists:users,id|unique:estudantes,user_id,' . $this->route('estudante')?->id
        ];
    }
}

