<?php

namespace App\Services\Presenca\Geocerca;

/**
 * Uma leitura da Geolocation API do navegador.
 *
 * Sao valores declarados pelo aparelho: podem ter sido simulados, e assina-los
 * com a chave do dispositivo nao os torna verdadeiros. A precisao e uma
 * estimativa horizontal em metros, nao uma garantia.
 */
final class Localizacao
{
    public function __construct(
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly float $precisaoMetros,
    ) {}
}
