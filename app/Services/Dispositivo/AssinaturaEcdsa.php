<?php

namespace App\Services\Dispositivo;

/**
 * Verificacao das assinaturas ECDSA P-256 / SHA-256 feitas pela Web Crypto API.
 *
 * O navegador exporta a chave publica em SPKI (DER) e entrega a assinatura no
 * formato P1363: os 32 bytes de r seguidos dos 32 de s. O openssl_verify() do
 * PHP espera a assinatura em ASN.1/DER, entao ela e convertida aqui antes.
 */
class AssinaturaEcdsa
{
    private const CURVA = 'prime256v1';

    private const TAMANHO_P1363 = 64;

    /**
     * Valida a chave publica enviada pelo navegador e devolve-a em DER. So
     * aceita chave EC na curva P-256; qualquer outra coisa volta nula.
     */
    public function chavePublica(string $spkiBase64): ?string
    {
        $der = $this->decodificar($spkiBase64);

        // Uma SPKI P-256 tem 91 bytes; a folga cobre a forma comprimida.
        if ($der === null || strlen($der) < 50 || strlen($der) > 200) {
            return null;
        }

        $chave = openssl_pkey_get_public($this->pem($der));

        if ($chave === false) {
            return null;
        }

        $detalhes = openssl_pkey_get_details($chave);

        if (($detalhes['type'] ?? null) !== OPENSSL_KEYTYPE_EC || ($detalhes['ec']['curve_name'] ?? null) !== self::CURVA) {
            return null;
        }

        return $der;
    }

    public function hashDaChave(string $der): string
    {
        return hash('sha256', $der);
    }

    /**
     * Confere a assinatura P1363 (base64 ou base64url) da mensagem.
     */
    public function verificar(string $chaveDer, string $mensagem, string $assinaturaBase64): bool
    {
        $assinatura = $this->decodificar($assinaturaBase64);

        if ($assinatura === null || strlen($assinatura) !== self::TAMANHO_P1363) {
            return false;
        }

        return openssl_verify($mensagem, $this->p1363ParaDer($assinatura), $this->pem($chaveDer), OPENSSL_ALGO_SHA256) === 1;
    }

    /**
     * r || s  ->  SEQUENCE { INTEGER r, INTEGER s }
     */
    public function p1363ParaDer(string $assinatura): string
    {
        $metade = intdiv(strlen($assinatura), 2);
        $corpo = $this->inteiroDer(substr($assinatura, 0, $metade)).$this->inteiroDer(substr($assinatura, $metade));

        return "\x30".chr(strlen($corpo)).$corpo;
    }

    /**
     * INTEGER do DER: sem zeros a esquerda e, se o primeiro bit estiver
     * ligado, com um 0x00 na frente para nao virar numero negativo.
     */
    private function inteiroDer(string $bytes): string
    {
        $bytes = ltrim($bytes, "\x00");

        if ($bytes === '' || (ord($bytes[0]) & 0x80)) {
            $bytes = "\x00".$bytes;
        }

        return "\x02".chr(strlen($bytes)).$bytes;
    }

    /**
     * Aceita base64 padrao ou base64url, com ou sem "=".
     */
    public function decodificar(string $valor): ?string
    {
        $valor = rtrim(strtr(trim($valor), '-_', '+/'), '=');

        if ($valor === '' || ! preg_match('/^[A-Za-z0-9+\/]+$/', $valor) || strlen($valor) % 4 === 1) {
            return null;
        }

        $decodificado = base64_decode($valor.str_repeat('=', (4 - strlen($valor) % 4) % 4), true);

        return $decodificado === false ? null : $decodificado;
    }

    private function pem(string $der): string
    {
        return "-----BEGIN PUBLIC KEY-----\n".chunk_split(base64_encode($der), 64, "\n")."-----END PUBLIC KEY-----\n";
    }
}
