<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pedido para vincular o primeiro navegador ou trocar o atual.
 *
 * Conhecer a senha nao basta: o atendimento confere a identidade e aprova, e
 * a aprovacao vale por pouco tempo, para uma tentativa concreta.
 */
class SolicitacaoDispositivo extends Model
{
    public const VINCULACAO = 'Vinculacao';

    public const TROCA = 'Troca';

    public const PENDENTE = 'Pendente';

    /** Identidade conferida; o estudante conclui o vinculo ate o prazo. */
    public const APROVADA = 'Aprovada';

    public const REJEITADA = 'Rejeitada';

    public const CONCLUIDA = 'Concluida';

    public const CANCELADA = 'Cancelada';

    /** Aprovada, mas o prazo para concluir passou. */
    public const EXPIRADA = 'Expirada';

    public const EM_ABERTO = [self::PENDENTE, self::APROVADA];

    protected $table = 'solicitacoes_dispositivo';

    protected $fillable = [
        'estudante_id',
        'tipo',
        'status',
        'motivo',
        'analisada_por',
        'analisada_em',
        'parecer',
        'aprovacao_expira_em',
        'concluida_em',
    ];

    protected $casts = [
        'analisada_em' => 'datetime',
        'aprovacao_expira_em' => 'datetime',
        'concluida_em' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Mantem o indice unico de "uma solicitacao em aberto por estudante".
        static::saving(function (self $solicitacao) {
            $solicitacao->estudante_em_aberto_id = in_array($solicitacao->status, self::EM_ABERTO, true)
                ? $solicitacao->estudante_id
                : null;
        });
    }

    public function estudante()
    {
        return $this->belongsTo(Estudante::class, 'estudante_id');
    }

    public function analisadaPor()
    {
        return $this->belongsTo(User::class, 'analisada_por');
    }

    public function dispositivo()
    {
        return $this->hasOne(DispositivoEstudante::class, 'solicitacao_id');
    }

    public function estaEmAberto(): bool
    {
        return in_array($this->status, self::EM_ABERTO, true);
    }

    public function aprovacaoVenceu(): bool
    {
        return $this->status === self::APROVADA
            && ($this->aprovacao_expira_em === null || $this->aprovacao_expira_em->isPast());
    }

    public function podeConcluir(): bool
    {
        return $this->status === self::APROVADA && ! $this->aprovacaoVenceu();
    }
}
