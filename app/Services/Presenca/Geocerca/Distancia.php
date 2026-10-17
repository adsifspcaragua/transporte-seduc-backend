<?php

namespace App\Services\Presenca\Geocerca;

/**
 * Distancia em metros entre dois pontos pela formula de Haversine.
 *
 * Calculada aqui, e nao com ST_Distance_Sphere(), para nao depender de
 * recurso espacial do banco nem de servico de mapas. Para os raios em jogo
 * (centenas de metros) a diferenca entre os dois e desprezivel.
 */
final class Distancia
{
    /** Raio medio da Terra, o mesmo usado pelo MySQL. */
    private const RAIO_TERRA_METROS = 6370986.0;

    public static function metros(float $latitudeA, float $longitudeA, float $latitudeB, float $longitudeB): float
    {
        $dLat = deg2rad($latitudeB - $latitudeA);
        $dLon = deg2rad($longitudeB - $longitudeA);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($latitudeA)) * cos(deg2rad($latitudeB)) * sin($dLon / 2) ** 2;

        return 2 * self::RAIO_TERRA_METROS * asin(min(1.0, sqrt($a)));
    }
}
