<?php

namespace App\Http\Requests\Reecadastro\Publico;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class FinalizarReecadastroRequest extends FormRequest
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
            'token' => 'required|string',
            'possui_matricula' => 'required|boolean',
            'possui_cronograma' => 'required|boolean',
            'prazo_matricula' => 'sometimes|boolean',
            'prazo_cronograma' => 'sometimes|boolean',
            'aceite_veracidade' => 'accepted',
            'aceite_ciencia' => 'accepted',
        ];
    }

    /**
     * Sem a declaração de matrícula ou o cronograma, o estudante precisa pedir
     * formalmente o prazo adicional para entregar o documento depois.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->boolean('possui_matricula') && ! $this->boolean('prazo_matricula')) {
                $validator->errors()->add(
                    'prazo_matricula',
                    'Marque a solicitação de prazo adicional para a declaração de matrícula.',
                );
            }

            if (! $this->boolean('possui_cronograma') && ! $this->boolean('prazo_cronograma')) {
                $validator->errors()->add(
                    'prazo_cronograma',
                    'Marque a solicitação de prazo adicional para o cronograma de aulas.',
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'aceite_veracidade.accepted' => 'É necessário declarar a veracidade das informações.',
            'aceite_ciencia.accepted' => 'É necessário estar ciente das penalidades.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'token' => ['description' => 'Token devolvido pela consulta do CPF.', 'example' => 'No-example'],
            'possui_matricula' => ['description' => 'Informa se o estudante ja possui a declaracao de matricula.', 'example' => true],
            'possui_cronograma' => ['description' => 'Informa se o estudante ja possui o cronograma de aulas.', 'example' => true],
            'prazo_matricula' => ['description' => 'Pedido de prazo adicional para a declaracao de matricula. Obrigatorio quando nao possui o documento.', 'example' => false],
            'prazo_cronograma' => ['description' => 'Pedido de prazo adicional para o cronograma de aulas. Obrigatorio quando nao possui o documento.', 'example' => false],
            'aceite_veracidade' => ['description' => 'Declaracao de veracidade das informacoes.', 'example' => true],
            'aceite_ciencia' => ['description' => 'Ciencia das penalidades em caso de informacao falsa.', 'example' => true],
        ];
    }
}
