<?php

namespace App\Http\Requests\Estudante;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEstudanteRequest extends FormRequest
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
        $estudante = $this->route('estudante');
        $estudanteId = is_object($estudante) ? $estudante->id : $estudante;

        return [
            'name' => 'required|string|max:255',

            'email' => 'required|email|unique:estudantes,email,'.$estudanteId,

            'cpf' => 'required|string|size:11|unique:estudantes,cpf,'.$estudanteId,

            'birth_date' => 'required|date|before:today',

            'phone' => 'required|string|max:15|unique:estudantes,phone,'.$estudanteId,

            'address' => 'required|string|max:255',

            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',

            'days_of_week' => 'required|array',
            'days_of_week.*' => 'string',

            'observation' => 'nullable|string|max:1000',

            'status' => 'required|string|max:255',

            'linha_id' => 'nullable|integer',

            'user_id' => 'nullable|integer|exists:users,id|unique:estudantes,user_id,'.$estudanteId,

            'instituicao_id' => 'required|exists:instituicoes,id',

            'inscricao_id' => 'required|integer|exists:inscricoes,id',
        ];
    }
}
