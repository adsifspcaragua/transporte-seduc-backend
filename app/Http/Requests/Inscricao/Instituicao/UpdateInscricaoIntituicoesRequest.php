<?php

namespace App\Http\Requests\Inscricao\Instituicao;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInscricaoIntituicoesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'inscricao_id' => $this->route('inscricao_id'),
        ]);
    }
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'course' => 'sometimes|string|min:3|max:255',
            'semester'  => 'sometimes|string|min:1|max:50',
            'expected_completion' => 'sometimes|date|after_or_equal:today',
            'instituicao_id' => "sometimes|integer|exists:instituicoes,id",
            'shift' => 'sometimes|integer|in:1,2',
            'city_destination' => 'sometimes|string|min:3|max:255',
            'used_transport' => 'sometimes|boolean',
            'days_of_week'   => 'sometimes|array|min:1',
            'days_of_week.*' => 'integer|between:0,6',
            'has_scholarship' => 'sometimes|boolean',
            'scholarship_type' => 'nullable|string|min:3|max:255|required_if:has_scholarship,true',
            
        ];
    }
}
