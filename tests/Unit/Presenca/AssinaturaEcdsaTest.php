<?php

namespace Tests\Unit\Presenca;

use App\Services\Dispositivo\AssinaturaEcdsa;
use App\Services\Presenca\Geocerca\Distancia;
use PHPUnit\Framework\TestCase;
use Tests\Support\NavegadorDeTeste;

/**
 * TESTE ESTRUTURAL da verificacao criptografica.
 *
 * A Web Crypto assina em P1363 (r || s) e o openssl_verify() do PHP espera
 * DER. A conversao tem os casos de borda do INTEGER do ASN.1: byte alto
 * ligado (precisa de 0x00 na frente) e zeros a esquerda (precisam sair).
 */
class AssinaturaEcdsaTest extends TestCase
{
    private AssinaturaEcdsa $assinatura;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assinatura = new AssinaturaEcdsa;
    }

    public function test_aceita_assinatura_p1363_do_navegador(): void
    {
        $navegador = new NavegadorDeTeste;
        $chave = $this->assinatura->chavePublica($navegador->chavePublica());

        $this->assertNotNull($chave);
        $this->assertTrue($this->assinatura->verificar($chave, 'texto do desafio', $navegador->assinar('texto do desafio')));
    }

    public function test_recusa_texto_alterado_e_chave_de_outro_navegador(): void
    {
        $navegador = new NavegadorDeTeste;
        $chave = $this->assinatura->chavePublica($navegador->chavePublica());
        $outra = $this->assinatura->chavePublica((new NavegadorDeTeste)->chavePublica());

        $assinatura = $navegador->assinar('viagem:1');

        $this->assertFalse($this->assinatura->verificar($chave, 'viagem:2', $assinatura));
        $this->assertFalse($this->assinatura->verificar($outra, 'viagem:1', $assinatura));
    }

    public function test_recusa_assinatura_fora_do_formato_p1363(): void
    {
        $navegador = new NavegadorDeTeste;
        $chave = $this->assinatura->chavePublica($navegador->chavePublica());

        // DER direto, como sairia de um cliente que nao usa a Web Crypto.
        $this->assertFalse($this->assinatura->verificar($chave, 'oi', base64_encode($navegador->assinarDer('oi'))));
        $this->assertFalse($this->assinatura->verificar($chave, 'oi', NavegadorDeTeste::base64url(str_repeat("\x01", 63))));
        $this->assertFalse($this->assinatura->verificar($chave, 'oi', '***'));
    }

    /**
     * Varias assinaturas para passar pelos dois casos de borda: a conversao
     * de volta precisa reproduzir byte a byte o DER do openssl.
     */
    public function test_conversao_p1363_para_der_e_canonica(): void
    {
        $navegador = new NavegadorDeTeste;
        $altoLigado = false;

        for ($i = 0; $i < 40; $i++) {
            $der = $navegador->assinarDer("mensagem {$i}");
            $p1363 = NavegadorDeTeste::derParaP1363($der);

            $altoLigado = $altoLigado || (ord($p1363[0]) & 0x80) || (ord($p1363[32]) & 0x80);
            $this->assertSame(bin2hex($der), bin2hex($this->assinatura->p1363ParaDer($p1363)));
        }

        $this->assertTrue((bool) $altoLigado, 'Nenhuma assinatura exercitou o byte alto ligado.');
    }

    public function test_inteiro_com_zeros_a_esquerda_perde_os_zeros(): void
    {
        $r = str_repeat("\x00", 2).str_repeat("\x11", 30);
        $s = "\x80".str_repeat("\x22", 31);

        $der = $this->assinatura->p1363ParaDer($r.$s);

        // Corpo: (2 + 30) de r sem os zeros + (2 + 33) de s com o 0x00 = 0x43.
        $this->assertSame('3043'.'021e'.str_repeat('11', 30).'0221'.'0080'.str_repeat('22', 31), bin2hex($der));
    }

    public function test_so_aceita_chave_ec_p256(): void
    {
        $this->assertNull($this->assinatura->chavePublica((new NavegadorDeTeste([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'secp384r1',
        ]))->chavePublica()));

        $this->assertNull($this->assinatura->chavePublica((new NavegadorDeTeste([
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => 2048,
        ]))->chavePublica()));

        $this->assertNull($this->assinatura->chavePublica('nao-e-uma-chave'));
        $this->assertNull($this->assinatura->chavePublica(base64_encode(random_bytes(91))));
    }

    public function test_aceita_chave_em_base64url(): void
    {
        $spki = (new NavegadorDeTeste)->chavePublica();
        $base64url = rtrim(strtr($spki, '+/', '-_'), '=');

        $this->assertSame($this->assinatura->chavePublica($spki), $this->assinatura->chavePublica($base64url));
    }

    public function test_haversine_em_distancias_conhecidas(): void
    {
        // 0,001 grau de latitude ~ 111,2 m em qualquer longitude.
        $this->assertEqualsWithDelta(111.2, Distancia::metros(-23.620, -45.41, -23.621, -45.41), 0.5);
        $this->assertSame(0.0, Distancia::metros(-23.62, -45.41, -23.62, -45.41));
        // 1 grau de longitude no equador, como o ST_Distance_Sphere() do MySQL.
        $this->assertEqualsWithDelta(111_194.9, Distancia::metros(0.0, 0.0, 0.0, 1.0), 1.0);
    }
}
