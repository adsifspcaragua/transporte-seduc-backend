<?php

namespace App\Http\Requests\Inscricao;

use Illuminate\Validation\Rule;
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

    
    return [ 
            'name' => "sometimes|string|min:3|max:255",
            'cpf' => [
                'sometimes',
                'string',
                'size:11',
                Rule::unique('inscricoes', 'cpf')
                    ->ignore($this->route('inscricao'))
            ],
            'rg' => "sometimes|string|min:8|max:11",
            "father_name" => 'sometimes|string|min:3|max:255',
            "mother_name" => 'sometimes|string|min:3|max:255',
            'birth_date' => "sometimes|date|before:today|date_format:Y-m-d",
            
            'phone'=> ["sometimes","string", "max:15", Rule::unique('inscricoes', 'phone')
                    ->ignore($this->route('inscricao'))],
            'email' => [
                'sometimes',
                'email',
                Rule::unique('inscricoes', 'email')
                    ->ignore($this->route('inscricao'))
            ],
            'cep' => "sometimes|string|size:8",
            'address' => "sometimes|string|min:3|max:255",
            'neighborhood' => "sometimes|string|min:3|max:255",
            'city' => "sometimes|string|min:3|max:255",
            'number' => "sometimes|integer|min:1",
            'accepted_terms' => "sometimes|boolean",
            'accepted_terms_2' => "sometimes|boolean",
            'status' => "prohibited",
            "observation" => "nullable|string|min:3|max:255",
        ];
    }
}
