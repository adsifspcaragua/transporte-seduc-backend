<?php

namespace App\Services\Presenca\Geocerca;

use App\Models\Viagem;
use Illuminate\Support\Carbon;

/**
 * Como a localizacao informada e confrontada com a area da viagem.
 *
 * A estrategia ainda nao foi decidida (D01/D02): pontos fixos, regioes,
 * corredor da rota ou hibrido. O fluxo de presenca so conhece esta interface;
 * trocar a politica e registrar outra implementacao no AppServiceProvider,
 * sem mexer no resto. A precisao minima e conferida antes, para todas.
 */
interface PoliticaGeocerca
{
    /** Identificador gravado na presenca, para saber com que regra foi aceita. */
    public function nome(): string;

    public function avaliar(Viagem $viagem, Localizacao $localizacao, Carbon $agora): ResultadoGeocerca;
}
