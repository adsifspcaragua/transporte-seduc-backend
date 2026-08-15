<?php

namespace App\Http\Requests\Reecadastro\Publico;

use App\Models\SolicitacaoReecadastro;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AtualizarDadosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $solicitacao = SolicitacaoReecadastro::with('estudante')->find($this->route('solicitacao'));
        $estudanteId = $solicitacao?->estudante_id;
        $inscricaoId = $solicitacao?->estudante?->inscricao_id;

        return [
            'token' => 'required|string',
            'name' => 'required|string|min:3|max:255',
            'rg' => 'nullable|string|min:8|max:11',
            'father_name' => 'nullable|string|min:3|max:255',
            'mother_name' => 'required|string|min:3|max:255',
            'birth_date' => 'required|date|before:today|date_format:Y-m-d',
            'phone' => [
                'required',
                'string',
                'max:15',
                Rule::unique('inscricoes', 'phone')->ignore($inscricaoId),
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('inscricoes', 'email')->ignore($inscricaoId),
                Rule::unique('estudantes', 'email')->ignore($estudanteId),
            ],
            'cep' => 'required|string|size:8',
            'address' => 'required|string|min:3|max:255',
            'neighborhood' => 'required|string|min:3|max:255',
            'complement' => 'nullable|string|max:255',
            'city' => 'required|string|min:3|max:255',
            'number' => 'required|integer|min:1',
            'course' => 'required|string|min:3|max:255',
            'semester' => 'required|string|min:1|max:50',
            'expected_completion' => 'required|date',
            'instituicao_id' => 'required|integer|exists:instituicoes,id',
            'shift' => 'required|integer|in:1,2',
            'city_destination' => 'required|string|min:3|max:255',
            'used_transport' => 'required|boolean',
            'days_of_week' => 'required|array|min:1',
            'days_of_week.*' => 'integer|between:0,6',
            'has_scholarship' => 'required|boolean',
            'scholarship_type' => 'nullable|string|min:3|max:255|required_if:has_scholarship,true',
        ];
    }
}
