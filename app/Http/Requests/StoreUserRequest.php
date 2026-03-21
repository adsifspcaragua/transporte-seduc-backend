<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            "email" => "required|email|unique:users,email",
            "password" => "required|min:8",
            "cpf" => "string|min:11|max:11|unique:users,cpf",
            "matricula" => "integer|unique:users,matricula",
            "data_nascimento" => "date|before:today|date_format:Y-m-d",
        ];
    }
}
