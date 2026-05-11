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
            'name' => 'sometimes|string|max:255',

            'email' => 'sometimes|email|unique:estudantes,email,'.$estudanteId,

            'cpf' => 'sometimes|string|size:11|unique:estudantes,cpf,'.$estudanteId,

            'birth_date' => 'sometimes|date|before:today',

            'phone' => 'sometimes|string|max:15|unique:estudantes,phone,'.$estudanteId,

            'address' => 'sometimes|string|max:255',

            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',

            'days_of_week' => 'sometimes|array',
            'days_of_week.*' => 'string',

            'observation' => 'nullable|string|max:1000',

            'status' => 'sometimes|string|max:255',

            'linha_id' => 'nullable|integer',

            'user_id' => 'nullable|integer|exists:users,id|unique:estudantes,user_id,'.$estudanteId,

            'instituicao_id' => 'sometimes|exists:instituicoes,id',

            'inscricao_id' => 'sometimes|integer|exists:inscricoes,id',
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
            'start_time' => ['description' => 'Horario de inicio das aulas no formato HH:MM.', 'example' => '07:30'],
            'end_time' => ['description' => 'Horario de termino das aulas no formato HH:MM.', 'example' => '12:00'],
            'days_of_week' => ['description' => 'Dias da semana em que o estudante usa o transporte.', 'example' => ['segunda', 'terca']],
            'days_of_week.*' => ['description' => 'Dia da semana.', 'example' => 'segunda'],
            'observation' => ['description' => 'Observacao opcional sobre o estudante.', 'example' => 'Necessita embarque no ponto central.'],
            'status' => ['description' => 'Status atual do estudante.', 'example' => 'ATIVO'],
            'linha_id' => ['description' => 'ID da linha vinculada ao estudante.', 'example' => 1],
            'user_id' => ['description' => 'ID do usuario associado ao estudante.', 'example' => 1],
            'inscricao_id' => ['description' => 'ID da inscricao que originou o estudante.', 'example' => 1],
            'instituicao_id' => ['description' => 'ID da instituicao do estudante.', 'example' => 1],
        ];
    }
}
