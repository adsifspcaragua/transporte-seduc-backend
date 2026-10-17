<?php

namespace App\Models;

use App\Enums\CodigoPresenca;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/**
 * Cada tentativa de presenca, aceita ou negada, com o motivo. E a base da
 * auditoria, do suporte e do pedido de revisao do estudante.
 */
class TentativaPresenca extends Model
{
    use MassPrunable;

    public const RESOLVER_QR = 'resolver_qr';

    public const DESAFIO = 'desafio';

    public const CONFIRMACAO = 'confirmacao';

    public const REGISTRADA = 'Registrada';

    public const NEGADA = 'Negada';

    public const REVISAO_PENDENTE = 'Pendente';

    public const REVISAO_APROVADA = 'Aprovada';

    public const REVISAO_REJEITADA = 'Rejeitada';

    protected $table = 'tentativas_presenca';

    protected $fillable = [
        'estudante_id',
        'viagem_id',
        'veiculo_id',
        'dispositivo_id',
        'presenca_id',
        'etapa',
        'resultado',
        'codigo',
        'distancia_metros',
        'precisao_metros',
        'recebida_em',
        'revisao_status',
        'revisao_motivo',
        'revisao_solicitada_em',
        'revisao_analisada_por',
        'revisao_analisada_em',
        'revisao_parecer',
    ];

    protected $casts = [
        'codigo' => CodigoPresenca::class,
        'distancia_metros' => 'integer',
        'precisao_metros' => 'integer',
        'recebida_em' => 'datetime',
        'revisao_solicitada_em' => 'datetime',
        'revisao_analisada_em' => 'datetime',
    ];

    public function estudante()
    {
        return $this->belongsTo(Estudante::class, 'estudante_id');
    }

    public function viagem()
    {
        return $this->belongsTo(Viagem::class, 'viagem_id');
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function presenca()
    {
        return $this->belongsTo(Presenca::class, 'presenca_id');
    }

    public function revisaoAnalisadaPor()
    {
        return $this->belongsTo(User::class, 'revisao_analisada_por');
    }

    /**
     * O estudante pode pedir revisao de uma negativa que nao foi culpa dele
     * (GPS, horario, geocerca) e que aponta para uma viagem concreta.
     */
    public function admiteRevisao(): bool
    {
        return $this->resultado === self::NEGADA
            && $this->viagem_id !== null
            && $this->revisao_status === null
            && $this->codigo?->admiteRevisao() === true;
    }

    /**
     * Retencao das tentativas (D10). Sem prazo configurado, nada e apagado; um
     * pedido de revisao em aberto nunca e.
     */
    public function prunable(): Builder
    {
        $dias = config('presenca.retencao.tentativas_dias');

        if ($dias === null) {
            return static::whereRaw('1 = 0');
        }

        return static::where('recebida_em', '<', now()->subDays((int) $dias))
            ->where(fn ($q) => $q->whereNull('revisao_status')->orWhere('revisao_status', '!=', self::REVISAO_PENDENTE));
    }
}
