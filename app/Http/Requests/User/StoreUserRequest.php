<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'cpf' => 'string|min:11|max:11|unique:users,cpf',
            'matricula' => 'integer|unique:users,matricula',
            'data_nascimento' => 'date|before:today|date_format:Y-m-d',
            'role' => 'required|string|in:admin,gestor,operador,estudante',
            'ativo' => 'boolean',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name' => ['description' => 'Nome completo do usuario.', 'example' => 'Maria Silva'],
            'email' => ['description' => 'E-mail unico do usuario.', 'example' => 'maria@example.com'],
            'password' => ['description' => 'Senha com no minimo 8 caracteres.', 'example' => 'password123'],
            'cpf' => ['description' => 'CPF do usuario com 11 digitos.', 'example' => '12345678901'],
            'matricula' => ['description' => 'Numero de matricula do usuario.', 'example' => 12345],
            'data_nascimento' => ['description' => 'Data de nascimento no formato AAAA-MM-DD.', 'example' => '1990-05-10'],
            'role' => ['description' => 'Perfil do usuario: admin, gestor, operador ou estudante.', 'example' => 'operador'],
            'ativo' => ['description' => 'Indica se o usuario esta ativo. Padrao true.', 'example' => true],
        ];
    }
}
