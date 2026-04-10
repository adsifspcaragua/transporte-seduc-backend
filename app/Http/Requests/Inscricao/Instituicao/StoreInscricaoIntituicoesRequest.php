<?php

namespace App\Http\Requests\Inscricao\Instituicao;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInscricaoIntituicoesRequest extends FormRequest
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
    protected function prepareForValidation()
    {
        $this->merge([
            'inscricao_id' => $this->route('inscricao_id'),
        ]);
    }

    public function rules(): array
    {
        
        return [
            'course' => 'required|string|min:3|max:255',
            'semester'  => 'required|string|min:1|max:50',
            'expected_completion' => 'required|date|after_or_equal:today',
            'instituicao_id' => "required|integer|exists:instituicoes,id",
            'shift' => 'required|integer|in:1,2',
            'city_destination' => 'required|string|min:3|max:255',
            'used_transport' => 'required|boolean',
            'days_of_week'   => 'required|array|min:1',
            'days_of_week.*' => 'integer|between:0,6',
            'has_scholarship' => 'required|boolean',
            'scholarship_type' => 'nullable|string|min:3|max:255|required_if:has_scholarship,true',
            "inscricao_id" => 'required|integer|exists:inscricaos,id|unique:inscricao_instituicoes,inscricao_id',
            //'line_id'  => 'required|integer|exists:lines,id', MODIFICAR
        ];
    }
}
