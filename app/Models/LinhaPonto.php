<?php

namespace App\Models;

use App\Support\HorarioLocal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * O ponto dentro de uma linha e sentido, com o horario previsto de passagem
 * quando a operacao o conhece.
 */
class LinhaPonto extends Model
{
    protected $table = 'linha_ponto';

    protected $fillable = [
        'linha_id',
        'ponto_id',
        'sentido',
        'ordem',
        'horario_previsto',
        'janela_antes_min',
        'janela_depois_min',
    ];

    protected $casts = [
        'ordem' => 'integer',
        'janela_antes_min' => 'integer',
        'janela_depois_min' => 'integer',
    ];

    public function linha()
    {
        return $this->belongsTo(Linha::class, 'linha_id');
    }

    public function ponto()
    {
        return $this->belongsTo(PontoEmbarque::class, 'ponto_id');
    }

    /**
     * Sem horario previsto, o ponto aceita qualquer momento da janela da
     * viagem; com ele, so a janela propria do ponto naquele dia.
     */
    public function aceitaEm(Carbon $dia, Carbon $momento): bool
    {
        if ($this->horario_previsto === null) {
            return true;
        }

        $previsto = HorarioLocal::momento($dia, $this->horario_previsto);
        $antes = $this->janela_antes_min ?? (int) config('presenca.janela.antes_minutos');
        $depois = $this->janela_depois_min ?? (int) config('presenca.janela.depois_minutos');

        return $momento->betweenIncluded($previsto->copy()->subMinutes($antes), $previsto->copy()->addMinutes($depois));
    }
}
