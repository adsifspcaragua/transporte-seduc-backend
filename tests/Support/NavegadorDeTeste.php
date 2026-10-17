<?php

namespace Tests\Support;

use OpenSSLAsymmetricKey;
use RuntimeException;

/**
 * Faz nos testes o papel da Web Crypto API do navegador: gera o par ECDSA,
 * exporta a chave publica em SPKI e assina no formato P1363 (r || s), que e
 * o que o `crypto.subtle.sign()` entrega.
 */
final class NavegadorDeTeste
{
    private OpenSSLAsymmetricKey $chave;

    /**
     * @param  array<string, mixed>  $opcoes  Ex.: outra curva ou RSA, para testar recusa.
     */
    public function __construct(array $opcoes = ['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1'])
    {
        // Sem um openssl.cnf acessivel (comum no PHP para Windows) a geracao
        // de chaves falha; um arquivo minimo resolve em qualquer sistema.
        $configuracao = tempnam(sys_get_temp_dir(), 'sigtu-ossl');
        file_put_contents($configuracao, "[req]\ndistinguished_name=dn\n[dn]\n");

        $chave = openssl_pkey_new([...$opcoes, 'config' => $configuracao]);
        @unlink($configuracao);

        while (openssl_error_string() !== false) {
            // descarta avisos da configuracao padrao ausente
        }

        if ($chave === false) {
            throw new RuntimeException('Não foi possível gerar a chave de teste.');
        }

        $this->chave = $chave;
    }

    /** `crypto.subtle.exportKey('spki', ...)` em base64. */
    public function chavePublica(): string
    {
        $pem = openssl_pkey_get_details($this->chave)['key'];

        return preg_replace('/-----[A-Z ]+-----|\s+/', '', $pem);
    }

    /** `crypto.subtle.sign({name: 'ECDSA', hash: 'SHA-256'}, ...)` em base64url. */
    public function assinar(string $texto): string
    {
        openssl_sign($texto, $der, $this->chave, OPENSSL_ALGO_SHA256);

        return self::base64url(self::derParaP1363($der));
    }

    /** A assinatura como o openssl a entrega (DER), sem converter. */
    public function assinarDer(string $texto): string
    {
        openssl_sign($texto, $der, $this->chave, OPENSSL_ALGO_SHA256);

        return $der;
    }

    /**
     * SEQUENCE { INTEGER r, INTEGER s } -> r || s com 32 bytes cada.
     */
    public static function derParaP1363(string $der): string
    {
        $tamanhoR = ord($der[3]);
        $r = substr($der, 4, $tamanhoR);
        $tamanhoS = ord($der[5 + $tamanhoR]);
        $s = substr($der, 6 + $tamanhoR, $tamanhoS);

        return str_pad(ltrim($r, "\x00"), 32, "\x00", STR_PAD_LEFT)
            .str_pad(ltrim($s, "\x00"), 32, "\x00", STR_PAD_LEFT);
    }

    public static function base64url(string $bytes): string
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }
}
