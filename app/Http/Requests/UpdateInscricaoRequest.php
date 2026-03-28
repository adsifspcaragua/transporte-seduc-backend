<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInscricaoRequest extends FormRequest
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
        $inscricaoId = $this->route("inscricao")?->id;
        return [ 
            'name' => "nullable|string|min:3|max:255",
            'cpf' => "required|string|min:11|max:11|unique:inscricaos,cpf,{$inscricaoId}",
            'rg' => "nullable|string|min:8|max:11",
            'date_of_birth' => "date|before:today|date_format:Y-m-d",
            'phone'=> 'nullable|string|max:15',
            'email' => "required|email|unique:inscricaos,email",
            'cep' => "nullable|min:8|max:8",
            'address' => "nullable|string|min:3|max:255",
            'neighborhood' =>"nullable|string|min:3|max:255",
            'city' => "nullable|string|min:3|max:255",
            'number' => "nullable|integer|min:1",
            'accepted_terms' => "boolean",
            'accepted_terms_2' => "boolean",
            'status' => "prohibited",
        ];
    }
}
