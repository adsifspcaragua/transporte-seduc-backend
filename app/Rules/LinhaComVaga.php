<?php

namespace App\Rules;

use App\Models\Estudante;
use App\Models\Linha;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Impede alocar um estudante em uma linha que ja esta lotada.
 *
 * A capacidade da linha existia como numero no cadastro, mas nada a verificava:
 * dava para colocar mais estudantes do que o onibus comporta.
 *
 * So estudantes ativos ocupam vaga; inativo nao anda de onibus. E o proprio
 * estudante nao conta contra si mesmo, senao salvar o cadastro de quem ja esta
 * na linha cheia seria recusado sem que nada tivesse mudado.
 */
class LinhaComVaga implements ValidationRule
{
    public function __construct(private readonly ?int $estudanteId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $linha = Linha::find($value);

        if (! $linha) {
            return; // A regra `exists` cuida da linha inexistente.
        }

        $ocupacao = Estudante::where('linha_id', $linha->id)
            ->where('status', 'Ativo')
            ->when($this->estudanteId, fn ($query) => $query->whereKeyNot($this->estudanteId))
            ->count();

        if ($ocupacao >= $linha->max_capacity) {
            $fail("A linha {$linha->name} está lotada ({$ocupacao} de {$linha->max_capacity} lugares).");
        }
    }
}
