<?php

namespace App\Http\Requests\Estudante;

use App\Models\Estudante;
use App\Models\Inscricao;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CorrigirEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * O endereco nao pode ser de outra pessoa em nenhuma das tres tabelas. A
     * conta so entra quando o estudante tem uma, porque e ela que sera alterada.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $estudante = $this->estudante();
        $inscricao = $this->inscricao();

        return [
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('estudantes', 'email')->ignore($estudante?->id),
                Rule::unique('inscricoes', 'email')->ignore($inscricao?->id),
                ...($estudante?->user_id ? [Rule::unique('users', 'email')->ignore($estudante->user_id)] : []),
            ],
            'justificativa' => 'required|string|min:10|max:1000',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'justificativa.required' => 'Registre como a identidade do estudante foi conferida.',
        ];
    }

    public function estudante(): ?Estudante
    {
        return $this->route('estudante') ?? $this->route('inscricao')?->estudante;
    }

    public function inscricao(): ?Inscricao
    {
        return $this->route('inscricao') ?? $this->route('estudante')?->inscricao;
    }

    public function bodyParameters(): array
    {
        return [
            'email' => ['description' => 'Novo e-mail do estudante.', 'example' => 'joao.novo@example.com'],
            'justificativa' => ['description' => 'Como a identidade foi conferida. Vai para a auditoria.', 'example' => 'RG conferido presencialmente no atendimento.'],
        ];
    }
}
