<?php

namespace App\Services\Viagem;

use App\Enums\CodigoPresenca;
use App\Exceptions\PresencaNegada;
use App\Models\Estudante;
use App\Models\Veiculo;
use App\Models\Viagem;
use App\Support\HorarioLocal;
use Illuminate\Support\Carbon;

/**
 * Descobre a viagem a partir da van lida, do estudante e do relogio do
 * servidor (TripResolver do roadmap).
 *
 * O frontend nunca informa a viagem: ela e deduzida aqui, tanto na leitura do
 * QR quanto de novo na confirmacao.
 */
class ResolvedorViagem
{
    public function __construct(
        private readonly GeradorViagens $gerador,
        private readonly AutorizacaoEstudante $autorizacao,
    ) {}

    /**
     * @throws PresencaNegada
     */
    public function resolver(Veiculo $veiculo, Estudante $estudante, Carbon $agora): Viagem
    {
        if (! $veiculo->ativo) {
            throw new PresencaNegada(CodigoPresenca::QR_INVALID);
        }

        // A janela da ultima viagem da noite pode passar da meia-noite: de
        // ontem, so entra a viagem cuja janela ainda alcanca hoje. "Hoje" e o
        // dia da operacao, nao o do fuso da aplicacao.
        $hoje = HorarioLocal::dia($agora);
        $ontem = $hoje->copy()->subDay();

        $this->gerador->gerar($ontem, $hoje);

        $daVan = Viagem::with('linha:id,name')
            ->where('veiculo_id', $veiculo->id)
            ->where('status', Viagem::PROGRAMADA)
            ->whereDate('data', '>=', $ontem->toDateString())
            ->whereDate('data', '<=', $hoje->toDateString())
            ->where(fn ($q) => $q->whereDate('data', $hoje->toDateString())->orWhere('janela_fim', '>=', HorarioLocal::inicioDoDia($hoje)))
            ->orderBy('janela_inicio')
            ->get();

        if ($daVan->isEmpty()) {
            throw new PresencaNegada(CodigoPresenca::NO_MATCHING_TRIP);
        }

        $autorizadas = $daVan->filter(fn (Viagem $viagem) => $this->autorizacao->permite($estudante, $viagem->linha_id, $viagem->data));

        if ($autorizadas->isEmpty()) {
            throw new PresencaNegada(CodigoPresenca::LINE_NOT_AUTHORIZED);
        }

        $naJanela = $autorizadas->filter(fn (Viagem $viagem) => $viagem->aceitaEm($agora));

        if ($naJanela->count() === 1) {
            return $naJanela->first();
        }

        if ($naJanela->count() > 1) {
            // Janelas sobrepostas da mesma van: o QR nao decide sozinho. O
            // gerador aponta esses conflitos para a gestao corrigir.
            throw new PresencaNegada(CodigoPresenca::MANUAL_REVIEW_REQUIRED, $naJanela->first());
        }

        // Fora do horario: a viagem mais proxima vai junto para que, se foi
        // atraso real da van, o estudante possa pedir revisao dela.
        $maisProxima = $autorizadas->sortBy(fn (Viagem $viagem) => min(
            abs($agora->diffInSeconds($viagem->janela_inicio)),
            abs($agora->diffInSeconds($viagem->janela_fim)),
        ))->first();

        throw new PresencaNegada(CodigoPresenca::OUTSIDE_TIME_WINDOW, $maisProxima);
    }
}
