<?php

namespace App\Services\Viagem;

use App\Models\AutorizacaoLinha;
use App\Models\Estudante;
use Illuminate\Support\Carbon;

/**
 * Em quais linhas o estudante pode registrar presenca em um dia.
 *
 * A linha do cadastro vale sempre; as autorizacoes temporarias somam outras
 * linhas durante o seu periodo. Estudante sem beneficio ativo nao usa nenhuma.
 */
class AutorizacaoEstudante
{
    public function habilitado(Estudante $estudante): bool
    {
        return $estudante->situacao === 'Ativo';
    }

    /**
     * @return list<int>
     */
    public function linhas(Estudante $estudante, Carbon $dia): array
    {
        if (! $this->habilitado($estudante)) {
            return [];
        }

        return AutorizacaoLinha::where('estudante_id', $estudante->id)
            ->cobrindo($dia)
            ->pluck('linha_id')
            ->push($estudante->linha_id)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function permite(Estudante $estudante, int $linhaId, Carbon $dia): bool
    {
        return in_array($linhaId, $this->linhas($estudante, $dia), true);
    }
}
