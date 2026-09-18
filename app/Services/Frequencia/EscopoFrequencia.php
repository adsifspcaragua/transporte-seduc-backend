<?php

namespace App\Services\Frequencia;

use App\Models\Linha;
use App\Models\User;

/**
 * Quais linhas o usuario enxerga na frequencia.
 *
 * O motorista lanca e consulta so a chamada das linhas que conduz. Quem tem
 * `frequencias.todas` (secretaria) ve todas: e quem acompanha o conjunto e
 * corrige a folha quando o motorista nao consegue.
 *
 * A permissao de rota diz "pode mexer com frequencia"; este escopo diz "em quais
 * linhas". Sao coisas separadas: sem ele, qualquer motorista marcaria falta em
 * estudante de linha alheia so trocando o id na URL.
 */
class EscopoFrequencia
{
    /**
     * @return list<int>|null IDs das linhas permitidas, ou null quando todas.
     */
    public function linhas(User $user): ?array
    {
        if ($user->hasRole('admin') || $user->hasPermission('frequencias.todas')) {
            return null;
        }

        return Linha::where('motorista_id', $user->id)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    public function permiteLinha(User $user, int $linhaId): bool
    {
        $linhas = $this->linhas($user);

        return $linhas === null || in_array($linhaId, $linhas, true);
    }
}
