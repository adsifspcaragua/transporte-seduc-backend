<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user')?->id;
        return [
            "email" => "required|email|unique:users,email,{$userId}",
            "name" => "required|string|max:255",
            "password" => "required|min:8",
            "cpf" =>  [
                'string',
                'min:11',
                'max:11',
                Rule::unique('users', 'cpf')->ignore($userId),
            ],
            "matricula" => [
                "integer",
                Rule::unique('users', 'matricula')->ignore($userId),],
            "data_nascimento" => "date|before:today|date_format:Y-m-d",
            
        ];
    }
}
