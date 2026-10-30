<?php

namespace Tests\Feature\Estudante;

use App\Mail\Acesso\CodigoAcessoMail;
use App\Models\CodigoAcesso;
use App\Models\Estudante;
use App\Models\Inscricao;
use App\Models\PeriodoReecadastro;
use App\Models\SolicitacaoReecadastro;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL do segundo fator no acesso sem login.
 *
 * Saber o CPF de alguem nao pode entregar os dados dessa pessoa nem derrubar a
 * sessao dela: sem o codigo enviado ao e-mail do cadastro, nada sai.
 */
class CodigoAcessoTest extends TestCase
{
    use RefreshDatabase;

    private const ACESSO = '/api/area-estudante/acesso';

    private const RECADASTRO = '/api/reecadastro/consulta';

    private const CPF = '52998224725';

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->travelTo(now());
    }

    private function inscricao(): Inscricao
    {
        return Inscricao::factory()->create([
            'status' => 'Incompleto',
            'cpf' => self::CPF,
            'email' => 'ana.souza@gmail.com',
            'access_token' => 'token-do-dono',
        ]);
    }

    private function codigoEnviado(): string
    {
        $codigo = null;

        Mail::assertQueued(CodigoAcessoMail::class, function (CodigoAcessoMail $mail) use (&$codigo) {
            $codigo = $mail->codigo;

            return true;
        });

        return $codigo;
    }

    public function test_so_o_cpf_nao_entrega_dados_nem_token_e_nao_derruba_o_dono(): void
    {
        $inscricao = $this->inscricao();

        $resposta = $this->postJson(self::ACESSO, ['cpf' => self::CPF])
            ->assertOk()
            ->assertExactJson([
                'fluxo' => 'verificacao',
                'data' => [
                    'email_mascarado' => 'an***@g***.com',
                    'validade_minutos' => 10,
                    'reenvio_em_segundos' => 60,
                ],
                'message' => 'Enviamos um código para o e-mail do seu cadastro.',
            ]);

        $this->assertStringNotContainsString($inscricao->name, $resposta->getContent());
        $this->assertSame('token-do-dono', $inscricao->refresh()->access_token);

        Mail::assertQueued(CodigoAcessoMail::class, fn (CodigoAcessoMail $mail) => $mail->hasTo('ana.souza@gmail.com'));
    }

    public function test_codigo_certo_entrega_os_dados_uma_unica_vez(): void
    {
        $inscricao = $this->inscricao();
        $this->postJson(self::ACESSO, ['cpf' => self::CPF]);
        $codigo = $this->codigoEnviado();

        $this->postJson(self::ACESSO.'/verificar', ['cpf' => self::CPF, 'codigo' => $codigo])
            ->assertOk()
            ->assertJsonPath('fluxo', 'lista_espera')
            ->assertJsonPath('data.inscricao.id', $inscricao->id)
            ->assertJsonStructure(['data' => ['inscricao' => ['token']]]);

        $this->postJson(self::ACESSO.'/verificar', ['cpf' => self::CPF, 'codigo' => $codigo])
            ->assertStatus(422)
            ->assertJsonValidationErrors('codigo');
    }

    public function test_codigo_errado_nao_entrega_nada_e_nao_troca_o_token(): void
    {
        $inscricao = $this->inscricao();
        $this->postJson(self::ACESSO, ['cpf' => self::CPF]);
        $errado = $this->codigoEnviado() === '000000' ? '000001' : '000000';

        $this->postJson(self::ACESSO.'/verificar', ['cpf' => self::CPF, 'codigo' => $errado])
            ->assertStatus(422)
            ->assertJsonMissingPath('data');

        $this->assertSame('token-do-dono', $inscricao->refresh()->access_token);
    }

    public function test_cinco_erros_invalidam_o_codigo_mesmo_com_o_certo_depois(): void
    {
        $this->inscricao();
        $this->postJson(self::ACESSO, ['cpf' => self::CPF]);
        $codigo = $this->codigoEnviado();
        $errado = $codigo === '000000' ? '000001' : '000000';

        for ($i = 0; $i < 5; $i++) {
            $this->postJson(self::ACESSO.'/verificar', ['cpf' => self::CPF, 'codigo' => $errado])->assertStatus(422);
        }

        $this->postJson(self::ACESSO.'/verificar', ['cpf' => self::CPF, 'codigo' => $codigo])->assertStatus(422);
    }

    public function test_codigo_vale_por_dez_minutos(): void
    {
        $this->inscricao();
        $this->postJson(self::ACESSO, ['cpf' => self::CPF]);
        $codigo = $this->codigoEnviado();

        $this->travel(11)->minutes();

        $this->postJson(self::ACESSO.'/verificar', ['cpf' => self::CPF, 'codigo' => $codigo])->assertStatus(422);
    }

    public function test_codigo_de_um_cpf_nao_serve_para_outro(): void
    {
        $this->inscricao();
        $outra = Inscricao::factory()->create(['status' => 'Incompleto']);
        $this->postJson(self::ACESSO, ['cpf' => self::CPF]);

        $this->postJson(self::ACESSO.'/verificar', ['cpf' => $outra->cpf, 'codigo' => $this->codigoEnviado()])->assertStatus(422);
    }

    public function test_pedir_de_novo_logo_em_seguida_nao_gera_outro_email(): void
    {
        $this->inscricao();

        $this->postJson(self::ACESSO, ['cpf' => self::CPF])->assertOk();
        $this->postJson(self::ACESSO, ['cpf' => self::CPF])->assertOk();

        Mail::assertQueued(CodigoAcessoMail::class, 1);
    }

    public function test_novo_codigo_invalida_o_anterior(): void
    {
        $this->inscricao();
        $this->postJson(self::ACESSO, ['cpf' => self::CPF]);
        $primeiro = $this->codigoEnviado();

        $this->travel(61)->seconds();
        $this->postJson(self::ACESSO, ['cpf' => self::CPF])->assertOk();

        $this->assertSame(1, CodigoAcesso::whereNull('usado_em')->count());
        Mail::assertQueued(CodigoAcessoMail::class, 2);
        $this->assertFalse(hash_equals(
            CodigoAcesso::whereNull('usado_em')->sole()->codigo_hash,
            hash_hmac('sha256', self::CPF.'|'.$primeiro, (string) config('app.key')),
        ));
    }

    public function test_limite_de_envios_por_hora_protege_o_email_do_estudante(): void
    {
        $this->inscricao();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson(self::ACESSO, ['cpf' => self::CPF])->assertOk();
            $this->travel(61)->seconds();
        }

        $this->postJson(self::ACESSO, ['cpf' => self::CPF])->assertStatus(429);
        Mail::assertQueued(CodigoAcessoMail::class, 5);

        $this->travel(1)->hours();
        $this->postJson(self::ACESSO, ['cpf' => self::CPF])->assertOk();
    }

    public function test_cadastro_sem_email_orienta_a_procurar_a_responsavel(): void
    {
        Inscricao::factory()->create(['status' => 'Incompleto', 'cpf' => self::CPF, 'email' => null]);

        $this->postJson(self::ACESSO, ['cpf' => self::CPF])->assertStatus(409);
        Mail::assertNothingQueued();
    }

    public function test_cpf_sem_cadastro_vai_direto_para_a_inscricao(): void
    {
        $this->postJson(self::ACESSO, ['cpf' => self::CPF])
            ->assertOk()
            ->assertJsonPath('fluxo', 'inscricao');

        Mail::assertNothingQueued();
    }

    public function test_recadastro_tambem_exige_o_codigo(): void
    {
        PeriodoReecadastro::factory()->aberto()->create();
        $estudante = Estudante::factory()->create(['cpf' => self::CPF, 'email' => 'ana.souza@gmail.com']);

        $this->postJson(self::RECADASTRO, ['cpf' => self::CPF])
            ->assertOk()
            ->assertJsonPath('fluxo', 'verificacao')
            ->assertJsonMissingPath('data.token');

        // A solicitacao so nasce depois de provar quem e.
        $this->assertSame(0, SolicitacaoReecadastro::count());

        $this->postJson(self::RECADASTRO.'/verificar', ['cpf' => self::CPF, 'codigo' => $this->codigoEnviado()])
            ->assertOk()
            ->assertJsonPath('data.estudante.id', $estudante->id)
            ->assertJsonStructure(['data' => ['token', 'documentos']]);
    }

    public function test_codigo_nao_fica_legivel_no_banco_nem_na_fila(): void
    {
        $this->inscricao();
        $this->postJson(self::ACESSO, ['cpf' => self::CPF]);

        $this->assertNotSame($this->codigoEnviado(), CodigoAcesso::sole()->codigo_hash);
        $this->assertSame(64, strlen(CodigoAcesso::sole()->codigo_hash));
        $this->assertInstanceOf(ShouldBeEncrypted::class, new CodigoAcessoMail('123456', 10));
    }

    public function test_email_mostra_o_codigo_e_a_validade(): void
    {
        $mail = new CodigoAcessoMail('482913', 10);

        $mail->assertSeeInHtml('482913');
        $mail->assertSeeInHtml('10 minutos');
    }
}
