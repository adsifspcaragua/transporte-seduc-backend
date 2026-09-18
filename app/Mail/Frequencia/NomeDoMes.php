<?php

namespace App\Mail\Frequencia;

/**
 * "2026-09" vira "setembro de 2026".
 *
 * O projeto nao traz traducoes pt_BR, entao o nome do mes nao pode vir do
 * locale do Carbon sem depender da configuracao da maquina.
 */
final class NomeDoMes
{
    private const MESES = [
        1 => 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho',
        'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro',
    ];

    public static function de(string $referencia): string
    {
        [$ano, $mes] = array_map('intval', explode('-', $referencia));

        return self::MESES[$mes].' de '.$ano;
    }
}
