<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Remanejamento: o estudante pode usar outra linha alem da do cadastro, por um
 * periodo. Fim nulo: sem prazo.
 */
class AutorizacaoLinha extends Model
{
    protected $table = 'autorizacoes_linha';

    protected $fillable = [
        'estudante_id',
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

    public function estudante()
    {
        return $this->belongsTo(Estudante::class, 'estudante_id');
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
}
