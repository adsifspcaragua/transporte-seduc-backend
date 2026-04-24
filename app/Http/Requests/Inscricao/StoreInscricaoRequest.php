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
        'name' => "sometimes|string|min:3|max:255",
        'cpf' => 'required|string|size:11|unique:inscricoes,cpf',
        'rg' => "nullable|string|min:8|max:11",
        "father_name" => 'sometimes|string|min:3|max:255',
        "mother_name" => 'sometimes|string|min:3|max:255',
        'birth_date' => "sometimes|date|before:today|date_format:Y-m-d",
        'phone'=> 'sometimes|string|max:15|unique:inscricoes,phone',
        'email' => 'sometimes|email|unique:inscricoes,email',
        'cep' => "sometimes|string|size:8",
        'address' => "sometimes|string|min:3|max:255",
        'neighborhood' => "sometimes|string|min:3|max:255",
        'complement' => "sometimes|string|min:3|max:255",
        'city' => "sometimes|string|min:3|max:255",
        'number' => "sometimes|integer|min:1",
        'accepted_terms' => "boolean",
        'accepted_terms_2' => "boolean",
        'status' => "prohibited",
        "observation" => "prohibited",
    ];
}
}
