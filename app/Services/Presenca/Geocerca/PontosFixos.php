<?php

namespace App\Services\Presenca\Geocerca;

use App\Models\LinhaPonto;
use App\Models\Viagem;
use Illuminate\Support\Carbon;

/**
 * Alternativa A do roadmap: pontos fixos de embarque, cada um com o seu raio.
 *
 * Valem os pontos ativos da linha no sentido da viagem. A leitura e aceita se
 * cair dentro do raio de algum deles e, quando o ponto tem horario previsto,
 * dentro da janela desse ponto. A precisao informada nao amplia o raio: GPS
 * ruim pede nova leitura, nao tolerancia maior.
 */
class PontosFixos implements PoliticaGeocerca
{
    public function nome(): string
    {
        return 'pontos.v1';
    }

    public function avaliar(Viagem $viagem, Localizacao $localizacao, Carbon $agora): ResultadoGeocerca
    {
        $pontos = LinhaPonto::with('ponto')
            ->where('linha_id', $viagem->linha_id)
            ->where('sentido', $viagem->sentido)
            ->whereHas('ponto', fn ($q) => $q->where('ativo', true))
            ->get();

        if ($pontos->isEmpty()) {
            return ResultadoGeocerca::semGeocerca();
        }

        $medidos = $pontos
            ->map(fn (LinhaPonto $linhaPonto) => [
                'linha_ponto' => $linhaPonto,
                'distancia' => (int) round(Distancia::metros(
                    $localizacao->latitude,
                    $localizacao->longitude,
                    $linhaPonto->ponto->latitude,
                    $linhaPonto->ponto->longitude,
                )),
            ])
            ->sortBy('distancia')
            ->values();

        $noRaio = $medidos->filter(fn ($medido) => $medido['distancia'] <= $medido['linha_ponto']->ponto->raio_metros);

        $noHorario = $noRaio->first(fn ($medido) => $medido['linha_ponto']->aceitaEm($viagem->data, $agora));

        if ($noHorario) {
            return ResultadoGeocerca::dentro($noHorario['linha_ponto']->ponto_id, $noHorario['distancia']);
        }

        if ($noRaio->isNotEmpty()) {
            $maisPerto = $noRaio->first();

            return ResultadoGeocerca::foraDoHorario($maisPerto['linha_ponto']->ponto_id, $maisPerto['distancia']);
        }

        $maisPerto = $medidos->first();

        return ResultadoGeocerca::fora($maisPerto['linha_ponto']->ponto_id, $maisPerto['distancia']);
    }
}
