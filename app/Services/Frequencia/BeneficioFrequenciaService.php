<?php

namespace App\Services\Frequencia;

use App\Mail\Frequencia\AvisoFaltasMail;
use App\Mail\Frequencia\BeneficioPerdidoMail;
use App\Models\AvisoFrequencia;
use App\Models\Chamada;
use App\Models\Estudante;
use App\Models\Frequencia;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Regra de permanencia no beneficio pela frequencia.
 *
 * O estudante perde o beneficio (fica Inativo) quando, dentro de um mesmo mes:
 *  - acumula 3 faltas seguidas, ou
 *  - acumula 5 faltas no total, seguidas ou intercaladas.
 *
 * A contagem recomeca a cada mes: faltas de agosto nao pesam em setembro, nem
 * para o total nem para a sequencia.
 *
 * O que conta como falta:
 *  - so marcacoes de chamadas fechadas. A folha aberta ainda e rascunho do
 *    motorista e pode mudar;
 *  - so a situacao "Falta". A justificada em analise nao conta ate a decisao,
 *    e a aprovada foi retirada. A rejeitada volta a ser "Falta" e passa a contar;
 *  - "seguidas" sao chamadas seguidas do estudante, e nao dias corridos: quem
 *    so usa o transporte as segundas e quartas falta "seguido" na segunda e na
 *    quarta. Uma presenca ou uma justificada interrompe a sequencia; um
 *    pendente e ignorado.
 *
 * Uma falta antes do limite, o estudante recebe um aviso por e-mail. Cada
 * aviso sai uma vez por mes; a perda do beneficio tambem e comunicada.
 */
class BeneficioFrequenciaService
{
    public const LIMITE_SEGUIDAS = 3;

    public const LIMITE_NO_MES = 5;

    /**
     * Reavalia os estudantes no mes informado.
     *
     * Chamada depois que uma folha e fechada ou que uma justificativa e
     * rejeitada. Uma falha com um estudante (e-mail, por exemplo) nao impede a
     * avaliacao dos demais nem desfaz o fechamento da folha.
     *
     * @param  iterable<int>  $estudanteIds
     */
    public function avaliar(iterable $estudanteIds, Carbon $mes): void
    {
        foreach (collect($estudanteIds)->unique() as $estudanteId) {
            try {
                $this->avaliarEstudante((int) $estudanteId, $mes);
            } catch (Throwable $e) {
                report($e);
            }
        }
    }

    /**
     * Como o estudante esta no mes, frente aos limites.
     *
     * @return array{referencia: string, faltas_no_mes: int, maior_sequencia: int, sequencia_atual: int, limite_no_mes: int, limite_seguidas: int, datas_das_faltas: list<string>}
     */
    public function situacao(Estudante $estudante, Carbon $mes): array
    {
        $marcacoes = Frequencia::query()
            ->join('chamadas', 'chamadas.id', '=', 'frequencias.chamada_id')
            ->where('frequencias.estudante_id', $estudante->id)
            ->where('chamadas.status', Chamada::FECHADA)
            ->whereBetween('chamadas.data', [
                $mes->copy()->startOfMonth()->toDateString(),
                $mes->copy()->endOfMonth()->toDateString(),
            ])
            ->orderBy('chamadas.data')
            ->get(['frequencias.situacao', 'chamadas.data']);

        $faltas = [];
        $maiorSequencia = 0;
        $sequencia = 0;

        foreach ($marcacoes as $marcacao) {
            if ($marcacao->situacao === Frequencia::PENDENTE) {
                continue;
            }

            if ($marcacao->situacao === Frequencia::FALTA) {
                $faltas[] = Carbon::parse($marcacao->data)->toDateString();
                $sequencia++;
                $maiorSequencia = max($maiorSequencia, $sequencia);

                continue;
            }

            $sequencia = 0;
        }

        return [
            'referencia' => $mes->format('Y-m'),
            'faltas_no_mes' => count($faltas),
            'maior_sequencia' => $maiorSequencia,
            // A sequencia que ainda esta correndo: e ela que chega ao limite na
            // proxima falta.
            'sequencia_atual' => $sequencia,
            'limite_no_mes' => self::LIMITE_NO_MES,
            'limite_seguidas' => self::LIMITE_SEGUIDAS,
            'datas_das_faltas' => $faltas,
        ];
    }

    private function avaliarEstudante(int $estudanteId, Carbon $mes): void
    {
        $estudante = Estudante::find($estudanteId);

        // Quem ja esta fora do beneficio nao tem o que perder nem por que ser avisado.
        if (! $estudante || $estudante->status !== 'Ativo') {
            return;
        }

        $situacao = $this->situacao($estudante, $mes);

        if ($situacao['faltas_no_mes'] >= self::LIMITE_NO_MES || $situacao['maior_sequencia'] >= self::LIMITE_SEGUIDAS) {
            $this->retirarBeneficio($estudante, $situacao);

            return;
        }

        $this->avisarSePerto($estudante, $situacao);
    }

    /**
     * @param  array<string, mixed>  $situacao
     */
    private function retirarBeneficio(Estudante $estudante, array $situacao): void
    {
        DB::transaction(function () use ($estudante, $situacao) {
            $estudante->update(['status' => 'Inativo']);
            $this->registrar($estudante, $situacao, AvisoFrequencia::BENEFICIO_PERDIDO);
        });

        $this->enviar($estudante, new BeneficioPerdidoMail($estudante, $situacao));
    }

    /**
     * Avisa quando falta uma falta para qualquer um dos limites. So o que ainda
     * nao foi avisado no mes gera e-mail.
     *
     * @param  array<string, mixed>  $situacao
     */
    private function avisarSePerto(Estudante $estudante, array $situacao): void
    {
        $motivos = [];

        if ($situacao['faltas_no_mes'] >= self::LIMITE_NO_MES - 1) {
            $motivos[] = AvisoFrequencia::PERTO_DO_LIMITE_NO_MES;
        }

        if ($situacao['sequencia_atual'] >= self::LIMITE_SEGUIDAS - 1) {
            $motivos[] = AvisoFrequencia::PERTO_DO_LIMITE_SEGUIDAS;
        }

        $novos = array_values(array_filter(
            $motivos,
            fn (string $tipo) => $this->registrar($estudante, $situacao, $tipo),
        ));

        if ($novos !== []) {
            $this->enviar($estudante, new AvisoFaltasMail($estudante, $situacao, $novos));
        }
    }

    /**
     * Grava a ocorrencia do mes. Devolve false se ela ja existia, o que evita
     * repetir o mesmo e-mail a cada chamada fechada.
     *
     * @param  array<string, mixed>  $situacao
     */
    private function registrar(Estudante $estudante, array $situacao, string $tipo): bool
    {
        $aviso = AvisoFrequencia::firstOrCreate(
            [
                'estudante_id' => $estudante->id,
                'referencia' => $situacao['referencia'],
                'tipo' => $tipo,
            ],
            [
                'faltas_no_mes' => $situacao['faltas_no_mes'],
                'faltas_seguidas' => $situacao['maior_sequencia'],
                'email' => $estudante->email,
            ],
        );

        return $aviso->wasRecentlyCreated;
    }

    /**
     * O e-mail vai para a fila: fechar a folha nao espera o servidor de e-mail,
     * e uma falha de envio e tentada de novo pelo worker.
     */
    private function enviar(Estudante $estudante, object $mensagem): void
    {
        if (! $estudante->email) {
            return;
        }

        Mail::to($estudante->email, $estudante->name)->queue($mensagem);
    }
}
