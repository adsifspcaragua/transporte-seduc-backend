<?php

namespace App\Services\Viagem;

use App\Models\HorarioViagem;
use App\Models\Linha;

/**
 * Mantem as duas representacoes do horario de uma linha em acordo.
 *
 * A fonte da verdade e `horarios_viagem`: e dele que saem as viagens.
 * `linhas.departure_time` e `return_time` continuam na API por compatibilidade,
 * como espelho do primeiro horario ativo de cada sentido. Editar a linha altera
 * o horario correspondente, e editar o horario atualiza a linha.
 */
class HorariosDaLinha
{
    private const CAMPOS = [
        HorarioViagem::IDA => 'departure_time',
        HorarioViagem::VOLTA => 'return_time',
    ];

    public function __construct(private readonly GeradorViagens $gerador) {}

    /**
     * Leva para a programacao o horario que acabou de ser gravado na linha.
     *
     * Sem horario no sentido, cria um com os dias padrao. Com um, atualiza a
     * saida e refaz as viagens futuras. Com mais de um, nao ha como saber qual
     * mudar: o sentido volta na lista e a linha volta a espelhar a programacao.
     *
     * @return list<string> Sentidos que nao puderam ser aplicados.
     */
    public function aplicarDaLinha(Linha $linha): array
    {
        $naoAplicados = [];

        foreach (self::CAMPOS as $sentido => $campo) {
            if (! $linha->wasChanged($campo) && ! $linha->wasRecentlyCreated) {
                continue;
            }

            $saida = $linha->getAttribute($campo);

            if ($saida === null) {
                continue;
            }

            $horarios = HorarioViagem::where('linha_id', $linha->id)
                ->where('sentido', $sentido)
                ->where('ativo', true)
                ->get();

            if ($horarios->count() > 1) {
                $naoAplicados[] = $sentido;

                continue;
            }

            if ($horarios->isEmpty()) {
                HorarioViagem::create([
                    'linha_id' => $linha->id,
                    'sentido' => $sentido,
                    'dias_semana' => config('presenca.dias_padrao'),
                    'horario_saida' => $saida,
                    'ativo' => true,
                ]);

                continue;
            }

            $horarios->first()->update(['horario_saida' => $saida]);
            $this->gerador->descartarFuturas($horarios->first());
        }

        if ($naoAplicados !== []) {
            $this->espelharNaLinha($linha->id);
            $linha->refresh();
        }

        return $naoAplicados;
    }

    /**
     * Copia para a linha o primeiro horario ativo de cada sentido. Sentido sem
     * horario ativo fica como esta: a linha nao perde a referencia.
     */
    public function espelharNaLinha(int $linhaId): void
    {
        $valores = [];

        foreach (self::CAMPOS as $sentido => $campo) {
            $saida = HorarioViagem::where('linha_id', $linhaId)
                ->where('sentido', $sentido)
                ->where('ativo', true)
                ->orderBy('horario_saida')
                ->value('horario_saida');

            if ($saida !== null) {
                $valores[$campo] = $saida;
            }
        }

        if ($valores !== []) {
            Linha::whereKey($linhaId)->update($valores);
        }
    }
}
