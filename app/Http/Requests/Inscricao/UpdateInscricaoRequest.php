<?php

namespace App\Http\Requests\Inscricao;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'name' => 'sometimes|string|min:3|max:255',
            'cpf' => [
                'sometimes',
                'string',
                'size:11',
                Rule::unique('inscricoes', 'cpf')
                    ->ignore($this->route('inscricao')),
            ],
            'rg' => 'sometimes|string|min:8|max:11',
            'father_name' => 'sometimes|string|min:3|max:255',
            'mother_name' => 'sometimes|string|min:3|max:255',
            'birth_date' => 'sometimes|date|before:today|date_format:Y-m-d',

            'phone' => ['sometimes', 'string', 'max:15', Rule::unique('inscricoes', 'phone')
                ->ignore($this->route('inscricao'))],
            'email' => [
                'sometimes',
                'email',
                Rule::unique('inscricoes', 'email')
                    ->ignore($this->route('inscricao')),
            ],
            'cep' => 'sometimes|string|size:8',
            'address' => 'sometimes|string|min:3|max:255',
            'neighborhood' => 'sometimes|string|min:3|max:255',
            'complement' => 'sometimes|string|min:3|max:255',
            'city' => 'sometimes|string|min:3|max:255',
            'number' => 'sometimes|integer|min:1',
            'accepted_terms' => 'sometimes|boolean',
            'accepted_terms_2' => 'sometimes|boolean',
            'status' => 'prohibited',
            'observation' => 'nullable|string|min:3|max:255',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name' => ['description' => 'Nome completo do estudante.', 'example' => 'Joao da Silva'],
            'cpf' => ['description' => 'CPF do estudante com 11 digitos.', 'example' => '12345678901'],
            'rg' => ['description' => 'RG do estudante.', 'example' => '12345678'],
            'father_name' => ['description' => 'Nome do pai.', 'example' => 'Jose da Silva'],
            'mother_name' => ['description' => 'Nome da mae.', 'example' => 'Maria da Silva'],
            'birth_date' => ['description' => 'Data de nascimento no formato AAAA-MM-DD.', 'example' => '2005-08-15'],
            'phone' => ['description' => 'Telefone para contato.', 'example' => '77999999999'],
            'email' => ['description' => 'E-mail do estudante.', 'example' => 'joao@example.com'],
            'cep' => ['description' => 'CEP com 8 digitos.', 'example' => '45000000'],
            'address' => ['description' => 'Endereco residencial.', 'example' => 'Rua Principal'],
            'neighborhood' => ['description' => 'Bairro residencial.', 'example' => 'Centro'],
            'complement' => ['description' => 'Complemento do endereco.', 'example' => 'Casa'],
            'city' => ['description' => 'Cidade residencial.', 'example' => 'Vitoria da Conquista'],
            'number' => ['description' => 'Numero do endereco.', 'example' => 100],
            'accepted_terms' => ['description' => 'Aceite do primeiro termo.', 'example' => true],
            'accepted_terms_2' => ['description' => 'Aceite do segundo termo.', 'example' => true],
            'status' => ['description' => 'Campo controlado pelo sistema. Nao envie este campo.', 'example' => 'No-example'],
            'observation' => ['description' => 'Observacao da analise ou ajuste cadastral.', 'example' => 'Dados conferidos pela equipe.'],
        ];
    }
}
