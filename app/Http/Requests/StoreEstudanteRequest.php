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
        "cpf" => "required|string|size:11|unique:estudantes,cpf",
        "birth_date" => "required|date|before:today",
        "phone" => "required|string|max:15",
        "address" => "required|string|max:255",
        "start_time" => "required|date_format:H:i",
        "end_time" => "required|date_format:H:i|after:start_time",
        
        "days_of_week" => "required|array",
        "days_of_week.*" => "string",

        "observation" => "nullable|string|max:1000",

        // status NÃO deve vir do front
        "status" => "prohibited",

        "linha_id" => "nullable|integer",
        "user_id" => "nullable|integer|exists:users,id",
        "inscricao_id" => "required|integer|exists:inscricoes,id",
        "instituicao_id" => "required|exists:instituicoes,id",
    ];
    }
}


