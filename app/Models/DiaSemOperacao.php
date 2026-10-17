<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Feriado, recesso ou paralisacao. Linha nula vale para todas.
 */
class DiaSemOperacao extends Model
{
    protected $table = 'dias_sem_operacao';

    protected $fillable = [
        'data',
        'linha_id',
        'motivo',
        'criado_por',
    ];

    protected $casts = [
        'data' => 'date',
    ];

    public function linha()
    {
        return $this->belongsTo(Linha::class, 'linha_id');
    }

    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    public function alcanca(int $linhaId): bool
    {
        return $this->linha_id === null || (int) $this->linha_id === $linhaId;
    }
}
