<?php

namespace App\Http\Requests\Inscricao;

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
            'name' => 'required|string|min:3|max:255',
            'cpf' => 'required|string|size:11|unique:inscricoes,cpf',
            'rg' => 'sometimes|string|min:8|max:11',
            'father_name' => 'sometimes|string|min:3|max:255',
            'mother_name' => 'sometimes|string|min:3|max:255',
            'birth_date' => 'sometimes|date|before:today|date_format:Y-m-d',
            'phone' => 'sometimes|string|max:15|unique:inscricoes,phone',
            'email' => 'sometimes|email|unique:inscricoes,email',
            'cep' => 'sometimes|string|size:8',
            'address' => 'sometimes|string|min:3|max:255',
            'neighborhood' => 'sometimes|string|min:3|max:255',
            'complement' => 'sometimes|string|min:3|max:255',
            'city' => 'sometimes|string|min:3|max:255',
            'number' => 'sometimes|integer|min:1',
            'accepted_terms' => 'boolean',
            'accepted_terms_2' => 'boolean',
            'status' => 'prohibited',
            'observation' => 'prohibited',
        ];
    }

    public function bodyParameters(): array
    {
        return $this->parameterDescriptions();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function parameterDescriptions(): array
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
            'observation' => ['description' => 'Campo controlado pelo sistema na criacao. Nao envie este campo.', 'example' => 'No-example'],
        ];
    }
}
