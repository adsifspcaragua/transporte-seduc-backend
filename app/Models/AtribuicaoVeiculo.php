<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Qual van atende a linha em um periodo. Fim nulo: sem prazo.
 */
class AtribuicaoVeiculo extends Model
{
    protected $table = 'atribuicoes_veiculo';

    protected $fillable = [
        'veiculo_id',
        'linha_id',
        'inicio',
        'fim',
        'motivo',
        'criada_por',
    ];

    protected $casts = [
        'inicio' => 'date',
        'fim' => 'date',
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function linha()
    {
        return $this->belongsTo(Linha::class, 'linha_id');
    }

    public function criadaPor()
    {
        return $this->belongsTo(User::class, 'criada_por');
    }

    public function scopeCobrindo(Builder $query, Carbon $dia): Builder
    {
        return $query
            ->whereDate('inicio', '<=', $dia)
            ->where(fn ($q) => $q->whereNull('fim')->orWhereDate('fim', '>=', $dia));
    }

    /**
     * A van da linha no dia. Havendo mais de uma atribuicao no periodo, vale a
     * de inicio mais recente: e assim que a troca de um dia se sobrepoe a
     * atribuicao permanente.
     */
    public static function veiculoDaLinha(int $linhaId, Carbon $dia): ?int
    {
        $veiculoId = static::where('linha_id', $linhaId)
            ->cobrindo($dia)
            ->whereHas('veiculo', fn ($q) => $q->where('ativo', true))
            ->orderByDesc('inicio')
            ->orderByDesc('id')
            ->value('veiculo_id');

        return $veiculoId !== null ? (int) $veiculoId : null;
    }
}
