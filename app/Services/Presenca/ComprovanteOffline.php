<?php

namespace App\Services\Presenca;

use App\Services\Presenca\Geocerca\Localizacao;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * O comprovante que o navegador monta e assina quando esta sem conexao.
 *
 * Sem servidor nao ha desafio, entao o texto assinado e definido aqui, linha a
 * linha, e o navegador o monta exatamente assim (separador "\n", sem espaco):
 *
 *     SIGTU
 *     v1
 *     presenca-offline
 *     <uuid do comprovante, gerado no celular>
 *     dispositivo:<uuid do dispositivo autorizado>
 *     qr:<token lido do QR da van>
 *     capturado_em:<horario do celular em milissegundos desde 1970>
 *     local:<latitude>,<longitude>,<precisao em metros inteiros>
 *       ou
 *     local:erro:<permissao_negada|indisponivel|tempo_esgotado>
 *
 * O servidor le os dados deste texto, o mesmo que foi assinado: nao ha campos
 * soltos que possam divergir da assinatura. O proposito "presenca-offline" e
 * diferente do desafio online, entao uma assinatura nao serve no lugar da outra.
 *
 * Horario e localizacao sao declarados pelo celular. A assinatura prova qual
 * navegador os declarou, nao que sejam verdadeiros: por isso o comprovante so
 * entra para revisao, nunca vira presenca sozinho.
 */
final class ComprovanteOffline
{
    private const LINHAS = 8;

    private const TAMANHO_MAXIMO = 700;

    private function __construct(
        public readonly string $id,
        public readonly string $dispositivoId,
        public readonly string $qrToken,
        public readonly Carbon $capturadoEm,
        public readonly ?Localizacao $localizacao,
        public readonly ?string $erroLocalizacao,
    ) {}

    public static function ler(string $texto): ?self
    {
        if (strlen($texto) > self::TAMANHO_MAXIMO) {
            return null;
        }

        $linhas = explode("\n", $texto);

        if (count($linhas) !== self::LINHAS) {
            return null;
        }

        [$sigtu, $versao, $proposito, $id, $dispositivo, $qr, $capturado, $local] = $linhas;

        if ($sigtu !== 'SIGTU' || $versao !== 'v1' || $proposito !== 'presenca-offline' || ! Str::isUuid($id)) {
            return null;
        }

        if (! preg_match('/^dispositivo:(.+)$/', $dispositivo, $d) || ! Str::isUuid($d[1])) {
            return null;
        }

        if (! preg_match('/^qr:([A-Za-z0-9_-]{20,128})$/', $qr, $q) || ! preg_match('/^capturado_em:(\d{13})$/', $capturado, $c)) {
            return null;
        }

        $capturadoEm = Carbon::createFromTimestampMs((int) $c[1])->setTimezone(config('app.timezone'));

        if (preg_match('/^local:erro:(permissao_negada|indisponivel|tempo_esgotado)$/', $local, $e)) {
            return new self(strtolower($id), strtolower($d[1]), $q[1], $capturadoEm, null, $e[1]);
        }

        if (! preg_match('/^local:(-?\d{1,2}(?:\.\d{1,7})?),(-?\d{1,3}(?:\.\d{1,7})?),(\d{1,6})$/', $local, $l)) {
            return null;
        }

        [$latitude, $longitude, $precisao] = [(float) $l[1], (float) $l[2], (int) $l[3]];

        if (abs($latitude) > 90 || abs($longitude) > 180 || $precisao < 1) {
            return null;
        }

        return new self(
            strtolower($id),
            strtolower($d[1]),
            $q[1],
            $capturadoEm,
            new Localizacao($latitude, $longitude, $precisao),
            null,
        );
    }
}
