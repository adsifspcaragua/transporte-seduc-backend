<?php

namespace App\Http\Requests\Estudante;

use App\Models\Estudante;
use App\Rules\LinhaComVaga;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $inscricaoId = Estudante::find($estudanteId)?->inscricao_id;

        return [
            'name' => 'sometimes|string|max:255',

            'email' => [
                'sometimes',
                'nullable',
                'email',
                Rule::unique('estudantes', 'email')->ignore($estudanteId),
                Rule::unique('inscricoes', 'email')->ignore($inscricaoId),
            ],

            'cpf' => [
                'sometimes',
                'nullable',
                'string',
                'size:11',
                Rule::unique('estudantes', 'cpf')->ignore($estudanteId),
                Rule::unique('inscricoes', 'cpf')->ignore($inscricaoId),
            ],

            'birth_date' => 'sometimes|date|before:today',

            'phone' => 'sometimes|string|max:15|unique:estudantes,phone,'.$estudanteId,

            'address' => 'sometimes|string|max:255',
            'rg' => 'sometimes|nullable|string|min:8|max:11',
            'mother_name' => 'sometimes|nullable|string|max:255',
            'father_name' => 'sometimes|nullable|string|max:255',
            'cep' => 'sometimes|nullable|string|max:9',
            'city' => 'sometimes|nullable|string|max:255',
            'neighborhood' => 'sometimes|nullable|string|max:255',
            'number' => 'sometimes|nullable|string|max:50',
            'complement' => 'sometimes|nullable|string|max:255',

            'days_of_week' => 'sometimes|array',
            'days_of_week.*' => 'integer|between:0,6',

            'observation' => 'nullable|string|max:1000',

            'status' => 'sometimes|string|max:255',

            'linha_id' => [
                'nullable',
                'integer',
                'exists:linhas,id',
                new LinhaComVaga($this->route('estudante') instanceof Estudante
                    ? $this->route('estudante')->id
                    : (int) $this->route('estudante')),
            ],

            'user_id' => 'nullable|integer|exists:users,id|unique:estudantes,user_id,'.$estudanteId,

            'instituicao_id' => 'sometimes|exists:instituicoes,id',

            'inscricao_id' => 'sometimes|integer|exists:inscricoes,id',

            'course' => 'sometimes|nullable|string|max:255',
            'semester' => 'sometimes|nullable|string|max:255',
            'expected_completion' => 'sometimes|nullable|date',
            'shift' => 'sometimes|nullable|integer|in:1,2',
            'city_destination' => 'sometimes|nullable|string|max:255',
            'used_transport' => 'sometimes|nullable|boolean',
            'has_scholarship' => 'sometimes|nullable|boolean',
            'scholarship_type' => 'sometimes|nullable|string|max:255',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'linha_id.exists' => 'Linha não encontrada.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name' => ['description' => 'Nome completo do estudante.', 'example' => 'Joao da Silva'],
            'email' => ['description' => 'E-mail unico do estudante.', 'example' => 'joao@example.com'],
            'cpf' => ['description' => 'CPF do estudante com 11 digitos.', 'example' => '12345678901'],
            'birth_date' => ['description' => 'Data de nascimento do estudante.', 'example' => '2005-08-15'],
            'phone' => ['description' => 'Telefone para contato.', 'example' => '77999999999'],
            'address' => ['description' => 'Endereco do estudante.', 'example' => 'Rua Principal, 100'],
            'days_of_week' => ['description' => 'Dias da semana de uso do transporte, de 0 a 6.', 'example' => [1, 3, 5]],
            'days_of_week.*' => ['description' => 'Dia da semana, de 0 a 6.', 'example' => 1],
            'observation' => ['description' => 'Observacao opcional sobre o estudante.', 'example' => 'Necessita embarque no ponto central.'],
            'status' => ['description' => 'Status atual do estudante.', 'example' => 'ATIVO'],
            'linha_id' => ['description' => 'ID da linha vinculada ao estudante.', 'example' => 1],
            'user_id' => ['description' => 'ID do usuario associado ao estudante.', 'example' => 1],
            'inscricao_id' => ['description' => 'ID da inscricao que originou o estudante.', 'example' => 1],
            'instituicao_id' => ['description' => 'ID da instituicao do estudante.', 'example' => 1],
        ];
    }
}
