<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A marcacao de um estudante em uma chamada.
 */
class Frequencia extends Model
{
    use HasFactory;

    /** Estava na lista do dia e ainda nao foi marcado. */
    public const PENDENTE = 'Pendente';

    public const PRESENTE = 'Presente';

    public const FALTA = 'Falta';

    /**
     * Faltou com motivo informado. O motivo vira uma Justificativa: enquanto
     * esta em analise ou depois de aprovada, a falta nao conta. Rejeitada, a
     * marcacao volta a ser Falta.
     */
    public const JUSTIFICADA = 'Justificada';

    /**
     * O que o motorista pode marcar. "Pendente" nao entra: e o estado de quem
     * ainda nao foi marcado, nao uma resposta.
     */
    public const SITUACOES = [
        self::PRESENTE,
        self::FALTA,
        self::JUSTIFICADA,
    ];

    protected $table = 'frequencias';

    protected $fillable = [
        'chamada_id',
        'estudante_id',
        'situacao',
        'observacao',
        'marcada_por',
        'marcada_em',
    ];

    protected $casts = [
        'marcada_em' => 'datetime',
    ];

    public function chamada()
    {
        return $this->belongsTo(Chamada::class, 'chamada_id');
    }

    public function estudante()
    {
        return $this->belongsTo(Estudante::class, 'estudante_id');
    }

    public function marcadaPor()
    {
        return $this->belongsTo(User::class, 'marcada_por');
    }

    public function justificativa()
    {
        return $this->hasOne(Justificativa::class, 'frequencia_id');
    }
}
