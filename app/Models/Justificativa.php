<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * O motivo de uma falta, analisado pela responsavel.
 */
class Justificativa extends Model
{
    use HasFactory;

    public const EM_ANALISE = 'Em analise';

    /** A falta e retirada. */
    public const APROVADA = 'Aprovada';

    /** A justificativa e invalida e a falta volta a contar. */
    public const REJEITADA = 'Rejeitada';

    protected $table = 'justificativas';

    protected $fillable = [
        'frequencia_id',
        'motivo',
        'status',
        'enviada_por',
        'analisada_por',
        'analisada_em',
        'parecer',
    ];

    protected $casts = [
        'analisada_em' => 'datetime',
    ];

    public function frequencia()
    {
        return $this->belongsTo(Frequencia::class, 'frequencia_id');
    }

    public function enviadaPor()
    {
        return $this->belongsTo(User::class, 'enviada_por');
    }

    public function analisadaPor()
    {
        return $this->belongsTo(User::class, 'analisada_por');
    }

    public function foiAnalisada(): bool
    {
        return $this->status !== self::EM_ANALISE;
    }
}
