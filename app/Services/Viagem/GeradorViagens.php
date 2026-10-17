<?php

namespace App\Services\Viagem;

use App\Models\AtribuicaoVeiculo;
use App\Models\DiaSemOperacao;
use App\Models\HorarioViagem;
use App\Models\Viagem;
use App\Support\HorarioLocal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Transforma a programacao (horarios) em viagens datadas.
 *
 * E idempotente: rodar de novo para o mesmo periodo nao duplica nada (indice
 * unico horario + data). Por isso o resolvedor pode chama-lo a cada leitura
 * de QR para garantir as viagens do dia, sem depender de um agendador.
 */
class GeradorViagens
{
    /**
     * Os limites sao datas da operacao: so o dia de cada um importa.
     *
     * @return int Quantas viagens foram criadas agora.
     */
    public function gerar(Carbon $de, Carbon $ate, ?int $linhaId = null): int
    {
        $de = Carbon::parse($de->toDateString(), HorarioLocal::fuso());
        $ate = Carbon::parse($ate->toDateString(), HorarioLocal::fuso());

        $horarios = HorarioViagem::where('ativo', true)
            ->when($linhaId, fn ($q) => $q->where('linha_id', $linhaId))
            ->get();

        $semOperacao = DiaSemOperacao::whereDate('data', '>=', $de)->whereDate('data', '<=', $ate)->get();

        $existentes = Viagem::whereIn('horario_id', $horarios->pluck('id'))
            ->whereDate('data', '>=', $de)
            ->whereDate('data', '<=', $ate)
            ->get(['horario_id', 'data'])
            ->map(fn (Viagem $viagem) => $viagem->horario_id.'|'.$viagem->data->toDateString())
            ->flip();

        $criadas = 0;

        for ($dia = $de->copy(); $dia->lte($ate); $dia->addDay()) {
            foreach ($horarios as $horario) {
                if (! $horario->operaEm($dia) || $existentes->has($horario->id.'|'.$dia->toDateString())) {
                    continue;
                }

                if ($semOperacao->contains(fn (DiaSemOperacao $sem) => $sem->data->toDateString() === $dia->toDateString() && $sem->alcanca($horario->linha_id))) {
                    continue;
                }

                // "17:30" e hora local; a janela vai para o banco no fuso da aplicacao.
                $saida = HorarioLocal::momento($dia, $horario->horario_saida);

                // createOrFirst: duas leituras de QR simultaneas podem tentar
                // gerar a mesma viagem; o indice unico decide quem cria.
                $viagem = Viagem::createOrFirst(
                    ['horario_id' => $horario->id, 'data' => $dia->toDateString()],
                    [
                        'linha_id' => $horario->linha_id,
                        'sentido' => $horario->sentido,
                        'horario_saida' => $horario->horario_saida,
                        'janela_inicio' => $saida->copy()->subMinutes($horario->janelaAntes()),
                        'janela_fim' => $saida->copy()->addMinutes($horario->janelaDepois()),
                        'veiculo_id' => AtribuicaoVeiculo::veiculoDaLinha($horario->linha_id, $dia),
                        'status' => Viagem::PROGRAMADA,
                    ],
                );

                $criadas += $viagem->wasRecentlyCreated ? 1 : 0;
            }
        }

        return $criadas;
    }

    /**
     * Viagens programadas da mesma van com janelas sobrepostas. Nelas o QR nao
     * aponta uma viagem so; a gestao precisa trocar a van ou ajustar a janela.
     *
     * @return list<array{veiculo_id: int, data: string, viagens: list<int>}>
     */
    public function conflitos(Carbon $de, Carbon $ate): array
    {
        $conflitos = [];

        Viagem::where('status', Viagem::PROGRAMADA)
            ->whereNotNull('veiculo_id')
            ->whereDate('data', '>=', $de)
            ->whereDate('data', '<=', $ate)
            ->orderBy('janela_inicio')
            ->get()
            ->groupBy('veiculo_id')
            ->each(function (Collection $doVeiculo, $veiculoId) use (&$conflitos) {
                $doVeiculo->values()->each(function (Viagem $viagem, int $i) use ($doVeiculo, $veiculoId, &$conflitos) {
                    foreach ($doVeiculo->values()->slice($i + 1) as $outra) {
                        if ($viagem->sobrepoe($outra)) {
                            $conflitos[] = [
                                'veiculo_id' => (int) $veiculoId,
                                'data' => $viagem->data->toDateString(),
                                'viagens' => [$viagem->id, $outra->id],
                            ];
                        }
                    }
                });
            });

        return $conflitos;
    }

    /**
     * A viagem colidiria com outra da mesma van?
     */
    public function colide(Viagem $viagem): ?Viagem
    {
        if ($viagem->veiculo_id === null || ! $viagem->estaProgramada()) {
            return null;
        }

        return Viagem::where('veiculo_id', $viagem->veiculo_id)
            ->where('status', Viagem::PROGRAMADA)
            ->when($viagem->exists, fn ($q) => $q->whereKeyNot($viagem->id))
            ->where('janela_inicio', '<', $viagem->janela_fim)
            ->where('janela_fim', '>', $viagem->janela_inicio)
            ->first();
    }

    /**
     * Reaplica a atribuicao de van as viagens da linha que ainda nao terminaram
     * e cuja van nao foi trocada a mao. O passado nao muda: e historia.
     */
    public function sincronizarVeiculos(int $linhaId): int
    {
        $alteradas = 0;

        Viagem::where('linha_id', $linhaId)
            ->where('status', Viagem::PROGRAMADA)
            ->where('veiculo_substituido', false)
            ->where('janela_fim', '>=', now())
            ->get()
            ->each(function (Viagem $viagem) use (&$alteradas) {
                $veiculoId = AtribuicaoVeiculo::veiculoDaLinha($viagem->linha_id, $viagem->data);

                if ($veiculoId !== $viagem->veiculo_id) {
                    $viagem->update(['veiculo_id' => $veiculoId]);
                    $alteradas++;
                }
            });

        return $alteradas;
    }

    /**
     * Descarta as viagens futuras de um horario que ainda podem ser refeitas:
     * sem presenca e sem ajuste manual. As demais ficam como estao.
     */
    public function descartarFuturas(HorarioViagem $horario): int
    {
        return Viagem::where('horario_id', $horario->id)
            ->where('janela_inicio', '>', now())
            ->whereNull('alterada_por')
            ->whereDoesntHave('presencas')
            ->delete();
    }
}
