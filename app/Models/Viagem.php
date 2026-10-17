<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A ocorrencia datada de um horario. A presenca e registrada aqui.
 */
class Viagem extends Model
{
    use HasFactory;

    public const PROGRAMADA = 'Programada';

    /** Feriado, paralisacao ou decisao da gestao: nao aceita presenca. */
    public const CANCELADA = 'Cancelada';

    public const STATUS = [self::PROGRAMADA, self::CANCELADA];

    protected $table = 'viagens';

    protected $fillable = [
        'horario_id',
        'linha_id',
        'sentido',
        'data',
        'horario_saida',
        'janela_inicio',
        'janela_fim',
        'veiculo_id',
        'veiculo_substituido',
        'status',
        'motivo_alteracao',
        'alterada_por',
    ];

    protected $casts = [
        'data' => 'date',
        'janela_inicio' => 'datetime',
        'janela_fim' => 'datetime',
        'veiculo_substituido' => 'boolean',
    ];

    public function horario()
    {
        return $this->belongsTo(HorarioViagem::class, 'horario_id');
    }

    public function linha()
    {
        return $this->belongsTo(Linha::class, 'linha_id');
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function alteradaPor()
    {
        return $this->belongsTo(User::class, 'alterada_por');
    }

    public function presencas()
    {
        return $this->hasMany(Presenca::class, 'viagem_id');
    }

    public function estaProgramada(): bool
    {
        return $this->status === self::PROGRAMADA;
    }

    /** O relogio e sempre o do servidor; o do aparelho nao entra aqui. */
    public function aceitaEm(Carbon $momento): bool
    {
        return $momento->betweenIncluded($this->janela_inicio, $this->janela_fim);
    }

    public function sobrepoe(self $outra): bool
    {
        return $this->janela_inicio->lt($outra->janela_fim) && $outra->janela_inicio->lt($this->janela_fim);
    }
}
