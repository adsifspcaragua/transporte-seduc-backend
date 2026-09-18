<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * So um usuario com o perfil motorista, e ativo, pode conduzir uma linha.
 *
 * O vinculo e o que libera a chamada daquela linha para o usuario: aceitar
 * qualquer usuario daria acesso a folha para quem nao dirige o onibus.
 */
class UsuarioMotorista implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $user = User::with('roles')->find($value);

        if (! $user) {
            return; // A regra `exists` cuida do usuario inexistente.
        }

        if (! $user->hasRole('motorista')) {
            $fail("{$user->name} não tem o perfil de motorista.");

            return;
        }

        if (! $user->ativo) {
            $fail("{$user->name} está inativo.");
        }
    }
}
