<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/**
 * Desafio de uso unico assinado pelo navegador, no cadastro da chave ou na
 * confirmacao de uma presenca.
 */
class DesafioDispositivo extends Model
{
    use HasUuids, MassPrunable;

    public const VINCULACAO = 'vinculacao';

    public const PRESENCA = 'presenca';

    protected $table = 'desafios_dispositivo';

    protected $fillable = [
        'id',
        'estudante_id',
        'proposito',
        'versao',
        'desafio_hash',
        'solicitacao_id',
        'dispositivo_id',
        'viagem_id',
        'veiculo_id',
        'expira_em',
        'usado_em',
    ];

    protected $hidden = [
        'desafio_hash',
    ];

    protected $casts = [
        'versao' => 'integer',
        'expira_em' => 'datetime',
        'usado_em' => 'datetime',
    ];

    public function estudante()
    {
        return $this->belongsTo(Estudante::class, 'estudante_id');
    }

    public function viagem()
    {
        return $this->belongsTo(Viagem::class, 'viagem_id');
    }

    /** Usados ou vencidos ha mais tempo que a retencao: nao servem para nada. */
    public function prunable(): Builder
    {
        return static::where('expira_em', '<', now()->subHours((int) config('presenca.desafio.retencao_horas')));
    }
}
