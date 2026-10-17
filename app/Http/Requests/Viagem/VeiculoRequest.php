<?php

namespace App\Http\Requests\Viagem;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Cadastro (POST) e edicao (PUT/PATCH) da van.
 */
class VeiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('veiculo');
        $obrigatorio = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'identificacao' => [$obrigatorio, 'string', 'min:2', 'max:60', Rule::unique('veiculos', 'identificacao')->ignore($id)],
            'placa' => ['nullable', 'string', 'max:10', Rule::unique('veiculos', 'placa')->ignore($id)],
            'ativo' => 'sometimes|boolean',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'identificacao' => ['description' => 'Nome da van, como aparece para a operacao.', 'example' => 'Van 01'],
            'placa' => ['description' => 'Placa do veiculo.', 'example' => 'ABC1D23'],
            'ativo' => ['description' => 'Van inativa nao atende viagens e o QR dela deixa de valer.', 'example' => true],
        ];
    }
}
