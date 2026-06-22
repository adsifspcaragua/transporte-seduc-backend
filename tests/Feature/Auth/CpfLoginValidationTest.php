<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL (caixa-preta).
 *
 * Tecnicas aplicadas:
 *  - Particionamento em classes de equivalencia
 *  - Analise do valor-limite
 *
 * Alvo: validacao do campo "login" no endpoint POST /api/auth/token
 * (aceita e-mail OU CPF valido -- AuthService::resolveAuthenticatedUser).
 *
 * Classes de equivalencia do "login":
 *   VALIDAS:   e-mail bem formado | CPF valido (11 digitos + DV correto)
 *   INVALIDAS: nao e e-mail nem CPF | CPF com DV errado | CPF com digitos repetidos
 *
 * Observacao sobre as respostas esperadas:
 *   422 -> falha de validacao (login rejeitado pela regra)
 *   401 -> login aceito pela validacao, mas sem usuario correspondente
 */
class CpfLoginValidationTest extends TestCase
{
    use RefreshDatabase;

    // CPF valido conhecido (DV calculado): 111.444.777-35
    private const CPF_VALIDO = '11144477735';

    protected function setUp(): void
    {
        parent::setUp();

        // O throttle nao e o objeto deste teste; evita 429 ao repetir chamadas.
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function tentarLogin(array $payload): TestResponse
    {
        return $this->postJson('/api/auth/token', $payload + ['password' => 'senha-qualquer']);
    }

    // ---------- CLASSES VALIDAS (passam na validacao -> 401) ----------

    public function test_email_valido_passa_na_validacao(): void
    {
        $this->tentarLogin(['login' => 'usuario@example.com'])
            ->assertStatus(401)
            ->assertJsonPath('message', 'Credenciais inválidas');
    }

    public function test_cpf_valido_passa_na_validacao(): void
    {
        $this->tentarLogin(['login' => self::CPF_VALIDO])
            ->assertStatus(401)
            ->assertJsonPath('message', 'Credenciais inválidas');
    }

    public function test_cpf_valido_com_mascara_passa_na_validacao(): void
    {
        $this->tentarLogin(['login' => '111.444.777-35'])
            ->assertStatus(401)
            ->assertJsonPath('message', 'Credenciais inválidas');
    }

    // ---------- CLASSES INVALIDAS (rejeitadas -> 422) ----------

    public function test_texto_que_nao_e_email_nem_cpf_e_rejeitado(): void
    {
        $this->tentarLogin(['login' => 'fulano'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('login');
    }

    public function test_cpf_com_digito_verificador_errado_e_rejeitado(): void
    {
        // 11 digitos, mas DV invalido.
        $this->tentarLogin(['login' => '11144477700'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('login');
    }

    public function test_cpf_com_digitos_repetidos_e_rejeitado(): void
    {
        $this->tentarLogin(['login' => '00000000000'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('login');
    }

    public function test_login_ausente_e_rejeitado(): void
    {
        $this->postJson('/api/auth/token', ['password' => 'x'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('login');
    }

    // ---------- ANALISE DO VALOR-LIMITE (comprimento do CPF: 11 digitos) ----------

    /**
     * Limites do tamanho do CPF (deve ter exatamente 11 digitos).
     *  - 10 digitos (limite inferior - 1) -> invalido
     *  - 12 digitos (limite superior + 1) -> invalido
     * (o valor exatamente no limite, 11 digitos validos, e coberto acima.)
     */
    public function test_cpf_com_dez_digitos_e_rejeitado(): void
    {
        $this->tentarLogin(['login' => '1114447773'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('login');
    }

    public function test_cpf_com_doze_digitos_e_rejeitado(): void
    {
        $this->tentarLogin(['login' => '111444777350'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('login');
    }
}
