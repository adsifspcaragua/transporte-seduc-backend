<?php

namespace App\Http\Requests\Estudante;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEstudanteRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:estudantes,email',
            'cpf' => 'required|string|size:11|unique:estudantes,cpf',
            'birth_date' => 'required|date|before:today',
            'phone' => 'required|string|max:15',
            'address' => 'required|string|max:255',

            'days_of_week' => 'required|array',
            'days_of_week.*' => 'integer|between:0,6',

            'observation' => 'nullable|string|max:1000',

            // status NÃO deve vir do front
            'status' => 'prohibited',

            'linha_id' => 'nullable|integer',
            'user_id' => 'nullable|integer|exists:users,id',
            'inscricao_id' => 'required|integer|exists:inscricoes,id',
            'instituicao_id' => 'required|exists:instituicoes,id',
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
            'status' => ['description' => 'Campo controlado pelo sistema. Nao envie este campo.', 'example' => 'No-example'],
            'linha_id' => ['description' => 'ID da linha vinculada ao estudante.', 'example' => 1],
            'user_id' => ['description' => 'ID do usuario associado ao estudante.', 'example' => 1],
            'inscricao_id' => ['description' => 'ID da inscricao que originou o estudante.', 'example' => 1],
            'instituicao_id' => ['description' => 'ID da instituicao do estudante.', 'example' => 1],
        ];
    }
}
