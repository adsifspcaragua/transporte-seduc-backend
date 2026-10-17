<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * O relogio da operacao do transporte.
 *
 * Horarios de saida ("17:30"), datas de viagem e feriados sao do fuso de
 * Caraguatatuba; a aplicacao roda em outro (config/app.php, hoje UTC), e e
 * nele que os instantes vao para o banco e que now() responde. Sem converter,
 * a janela das 17h30 seria comparada com as 20h30 locais.
 *
 * Regra do modulo de presenca: datas e horas da programacao entram por aqui;
 * instantes (janelas, registros) ficam sempre no fuso da aplicacao.
 */
final class HorarioLocal
{
    public static function fuso(): string
    {
        return (string) config('presenca.fuso');
    }

    /** Hoje no fuso da operacao, a meia-noite local. */
    public static function hoje(): Carbon
    {
        return now(self::fuso())->startOfDay();
    }

    /** O dia local de um instante. */
    public static function dia(CarbonInterface $momento): Carbon
    {
        return Carbon::instance($momento)->setTimezone(self::fuso())->startOfDay();
    }

    /**
     * A hora local de um dia como instante no fuso da aplicacao — o valor que
     * vai para o banco e que se compara com now().
     */
    public static function momento(CarbonInterface|string $dia, string $hora): Carbon
    {
        $data = $dia instanceof CarbonInterface ? $dia->toDateString() : $dia;

        return Carbon::parse("{$data} {$hora}", self::fuso())->setTimezone(config('app.timezone'));
    }

    /** Uma data e hora digitadas na operacao (ex.: "2026-10-19 17:30"). */
    public static function digitado(string $dataHora): Carbon
    {
        return Carbon::parse($dataHora, self::fuso())->setTimezone(config('app.timezone'));
    }

    /** Meia-noite local do dia, para comparar com colunas de data e hora. */
    public static function inicioDoDia(CarbonInterface|string $dia): Carbon
    {
        return self::momento($dia, '00:00:00');
    }

    /** Instante exibido na hora local, ex.: auditoria. */
    public static function exibir(?CarbonInterface $momento): ?string
    {
        return $momento?->copy()->setTimezone(self::fuso())->toDateTimeString();
    }

    /** ISO 8601 com o deslocamento local (2026-10-19T17:15:00-03:00). */
    public static function iso(?CarbonInterface $momento): ?string
    {
        return $momento?->copy()->setTimezone(self::fuso())->toIso8601String();
    }
}
