<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Entrega um documento enviado pelo estudante, para visualizacao ou download.
 *
 * "Visualizar" abre o arquivo na aba; "Baixar" salva. A diferenca esta no
 * cabecalho Content-Disposition, e nao no link: o navegador obedece o cabecalho
 * mesmo quando o link pede target="_blank".
 *
 * Inscricao e recadastro entregam arquivos enviados por terceiros, entao a regra
 * de quais tipos podem ser exibidos vive aqui, em um lugar so.
 */
class EntregaDeArquivo
{
    /**
     * Tipos que o navegador exibe sem risco de executar script.
     *
     * SVG fica de fora de proposito: e XML e pode carregar script, que rodaria
     * no dominio da API se fosse servido inline.
     */
    private const EXIBIVEIS_INLINE = [
        'image/png',
        'image/jpeg',
        'image/gif',
        'image/webp',
        'image/bmp',
        'application/pdf',
    ];

    /**
     * Um tipo que o navegador nao exibe com seguranca volta a ser anexo, para
     * nao abrir uma aba em branco nem servir conteudo executavel.
     */
    public static function responder(string $caminho, string $nome, bool $inline): StreamedResponse
    {
        if ($inline && in_array(Storage::mimeType($caminho), self::EXIBIVEIS_INLINE, true)) {
            return Storage::response($caminho, $nome, [
                'Content-Disposition' => HeaderUtils::makeDisposition(
                    HeaderUtils::DISPOSITION_INLINE,
                    $nome,
                    basename($caminho),
                ),
            ]);
        }

        return Storage::download($caminho, $nome);
    }
}
