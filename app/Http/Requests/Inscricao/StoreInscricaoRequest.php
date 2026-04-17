<?php

namespace App\Http\Requests\Inscricao;

use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInscricaoRequest extends FormRequest
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
        'name' => "required|string|min:3|max:255",
        'cpf' => 'required|string|size:11|unique:inscricoes,cpf',
        'rg' => "nullable|string|min:8|max:11",
        "father_name" => 'required|string|min:3|max:255',
        "mother_name" => 'required|string|min:3|max:255',
        'birth_date' => "required|date|before:today|date_format:Y-m-d",
        'phone'=> 'nullable|string|max:15|unique:inscricoes,phone',
        'email' => 'required|email|unique:inscricoes,email',
        'cep' => "nullable|string|size:8",
        'address' => "nullable|string|min:3|max:255",
        'neighborhood' => "nullable|string|min:3|max:255",
        'city' => "nullable|string|min:3|max:255",
        'number' => "nullable|integer|min:1",
        'accepted_terms' => "boolean",
        'accepted_terms_2' => "boolean",
        'status' => "prohibited",
        "observation" => "prohibited",
    ];
}
}
