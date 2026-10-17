<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * A instalacao de navegador autorizada do estudante: a chave publica ECDSA
 * P-256 cuja privada ficou, nao exportavel, no IndexedDB do navegador.
 *
 * Nao identifica o aparelho fisico. Outro navegador ou perfil no mesmo
 * celular gera outra chave; por isso a regra e "uma credencial ativa por
 * estudante", e nao "um celular por estudante".
 */
class DispositivoEstudante extends Model
{
    use HasUuids;

    public const ATIVO = 'Ativo';

    public const REVOGADO = 'Revogado';

    protected $table = 'dispositivos_estudante';

    protected $fillable = [
        'estudante_id',
        'solicitacao_id',
        'chave_publica',
        'chave_publica_sha256',
        'rotulo',
        'status',
        'aprovado_por',
        'aprovado_em',
        'ultimo_uso_em',
        'revogado_em',
        'revogado_por',
        'motivo_revogacao',
    ];

    protected $hidden = [
        'chave_publica',
        'chave_publica_sha256',
        'estudante_ativo_id',
    ];

    protected $casts = [
        'aprovado_em' => 'datetime',
        'ultimo_uso_em' => 'datetime',
        'revogado_em' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Mantem o indice unico de "uma credencial ativa por estudante".
        static::saving(function (self $dispositivo) {
            $dispositivo->estudante_ativo_id = $dispositivo->status === self::ATIVO
                ? $dispositivo->estudante_id
                : null;
        });
    }

    public function estudante()
    {
        return $this->belongsTo(Estudante::class, 'estudante_id');
    }

    public function solicitacao()
    {
        return $this->belongsTo(SolicitacaoDispositivo::class, 'solicitacao_id');
    }

    public function aprovadoPor()
    {
        return $this->belongsTo(User::class, 'aprovado_por');
    }

    public function revogadoPor()
    {
        return $this->belongsTo(User::class, 'revogado_por');
    }

    public function estaAtivo(): bool
    {
        return $this->status === self::ATIVO;
    }

    /** A chave publica em DER, como o navegador a exportou (`spki`). */
    public function chaveDer(): string
    {
        return base64_decode($this->chave_publica, true) ?: '';
    }
}
